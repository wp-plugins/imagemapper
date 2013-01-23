/* ImageMapper Wordpress admin panel script
*/
var Image;
var Canvas, Ctx;
var SavedAreasCanvas, SACtx;
var Coords = [];
var ShiftPressedDown = false;
var E = { 
	Image: '#imagemap-image',
	CoordCanvas: '#image-coord-canvas'
	}

jQuery(function($) {
	
	if($(E.Image).length > 0) {
		$(E.Image).load(function() { $(this).show(200); });
		$(E.CoordCanvas).mousedown(imgClick);
		$(window).
		keydown(function(evt) { if(evt.which == 16) { ShiftPressedDown = true; } }).
		keyup(function(evt) { if(evt.which == 16) { ShiftPressedDown = false; } });
			
		
		$('#add-area-button').click(AddArea);
		var img = new Image();
		img.onload = function() {
			Image = { width: this.width, height: this.height };
			Canvas.width = Image.width;
			Canvas.height = Image.height;
			SavedAreasCanvas.width = Image.width;
			SavedAreasCanvas.height = Image.height;
			
			$(Canvas).width($(E.Image).width());
			$(Canvas).height($(E.Image).height());
			$(SavedAreasCanvas).width($(E.Image).width());
			$(SavedAreasCanvas).height($(E.Image).height());
		};
		img.src = $(E.Image).attr('src');
		
		Canvas = document.getElementById('image-coord-canvas');
		SavedAreasCanvas = document.getElementById('image-area-canvas');
		if(Canvas) {
			Ctx = Canvas.getContext('2d');
			SACtx = SavedAreasCanvas.getContext('2d');
			DrawSavedAreas(SavedAreasCanvas, SACtx);
			$('.area-list-element').change(function() { DrawSavedAreas(SavedAreasCanvas, SACtx); 3});
		}
		$('.delete-area').click(DeleteArea);
		
		$('#imagemap-image-container').attr('data-initpos', $('#imagemap-image-container').offset().top);
		
		/* Scrolling effect so that the image on Image map page would scroll with the screen when scrolling a large amount of image map areas
		 * in the list on the right 
		 * It didn't end up in high quality and the idea was rather bad. Possibly going to remove this in future releases*/
		 /*
		$(window).scroll(function() {
			var element = $('#imagemap-image-container');
			var topPosition = $(window).scrollTop() - element.attr('data-initpos') + 35;
			var cssTop = parseInt(element.css('top'));
			if(isNaN(cssTop)) { cssTop = 0; }
			if(!((cssTop < topPosition) && (cssTop + element.height() > topPosition + $(window).height()))) {
			// !(cssTop < topPosition) != 
			// !(cssTop + element.height() > topPosition + $(window).height())) {
				var val = 0;
				if(cssTop + element.height() > topPosition + $(window).height())
					val = topPosition - element.height() + $(window).height() - 50;
				else
					val = topPosition;
				
				element.stop().animate({ top: 
					Math.max(0, Math.min(val, $('#poststuff').height() - element.height() - 114)) +'px' 
				}, 400);
			}
		}); */
		
	}
	$('.insert-media-imagemap').click(insertImageMap);
	
	$('.imgmap-color-picker').each(function() {
		$(this).farbtastic({
			callback: function(color) {
			},
			width: 100,
			height: 100
		});
	});
	
	$('#imgmap-area-styles .imgmap-area-style').click(ChooseStyleToUse);
	$('#imgmap-area-styles-edit .imgmap-area-style').click(ChooseStyleToEdit);
	
	$('#add-new-imgmap-style-button').click(AddNewStyle);
	$('#edit-imgmap-style-button').click(EditStyle);
	$('#delete-imgmap-style-button').click(DeleteStyle);
	
	function ChooseStyleToUse() {
		SetColor($(this).attr('data-id'));
		$('.imgmap-area-style').removeClass('chosen');
		$(this).addClass('chosen');
	}
	
	function ChooseStyleToEdit() {
		InitStyleEdit(this);
		$('#delete-imgmap-style-button, #edit-imgmap-style-button').attr('disabled', false);
		$('.imgmap-area-style').removeClass('chosen');
		$(this).addClass('chosen');
	}
	
	function AddNewStyle() {
		
		jQuery.post(ajaxurl, { 
			action: 'imgmap_add_new_style',
			fillColor: $('#imgmap-new-style-fillcolor').val(),
			fillOpacity: $('#imgmap-new-style-fillopacity').val(),
			strokeColor: $('#imgmap-new-style-strokecolor').val(),
			strokeOpacity: $('#imgmap-new-style-strokeopacity').val(),
			strokeWidth: $('#imgmap-new-style-strokewidth').val()
			}, function(response) {
				$('.imgmap-area-style').removeClass('chosen');
				$('#imgmap-area-styles-edit').append(response);
				$('.imgmap-area-style').click(ChooseStyleToEdit);
				alert('Style added');
		});
	}

	function EditStyle(id) {
		var id = jQuery(jQuery('.imgmap-area-style.chosen').get()).attr('data-id');
		
		jQuery.post(ajaxurl, { 
			action: 'imgmap_edit_style',
			id: id,
			fillColor: $('#imgmap-new-style-fillcolor').val(),
			fillOpacity: $('#imgmap-new-style-fillopacity').val(),
			strokeColor: $('#imgmap-new-style-strokecolor').val(),
			strokeOpacity: $('#imgmap-new-style-strokeopacity').val(),
			strokeWidth: $('#imgmap-new-style-strokewidth').val()
			}, function(response) {
				$('.imgmap-area-style.chosen').replaceWith(response);
				$('.imgmap-area-style').click(ChooseStyleToEdit);
				alert('Changes saved');
		});
	}

	function DeleteStyle(id) {
		if(!confirm('Are you sure you want to delete this style? All highlights currently using it will be changed to use a white default style'))
			return;
			
		var id = jQuery(jQuery('.imgmap-area-style.chosen').get()).attr('data-id');
		
		jQuery.post(ajaxurl, { 
			action: 'imgmap_delete_style',
			id: id
			}, function(response) {
			$('#imgmap-area-styles-edit').append(response);
				jQuery('.imgmap-area-style.chosen').hide(200, function() { 
					jQuery(this).remove();				
					$('#delete-imgmap-style-button, #edit-imgmap-style-button').attr('disabled', true);
				});
		});
	}
	if($().wpColorPicker)
		$('.color-picker-field').wpColorPicker();
});


function insertImageMap() {
	var img = jQuery(this).attr('data-imagemap');
	window.parent.send_to_editor('[imagemap id="'+img+'"]');
	window.parent.tb_remove();
}

function AreaClicked(data) {
}

function FileAPIAvailable() {
	return window.File && window.FileList && window.FileReader && window.Blob;
}

function ShowTypes(typeToShow) {
	jQuery('input[value="'+typeToShow+'"][name="area-type"]').attr('checked', true);
	jQuery('.area-type-editors, .area-type-instructions').hide();
	jQuery('#imagemap-area-'+typeToShow+'-editor').show(200);
}

function imgClick(evt) {
	switch(evt.which) {
		case 1: 
			if(ShiftPressedDown) {
				CoordsBack();
			} else {
				var offset = jQuery(this).offset();
				AddCoords(evt.pageX - offset.left, evt.pageY - offset.top);	
			}
		break;
	}
}
var coordinate_index = 0;
function AddCoords(x, y) {
	
	if(!Image.width) {
		alert("Source image haven't been downloaded yet! Please try again in few seconds.");
	}
	
	Coords.push({
	x: Math.floor(x * (Image.width/jQuery('#imagemap-image').width())),
	y: Math.floor(y * (Image.height/jQuery('#imagemap-image').height())),
	id: ++coordinate_index
	});
	
	jQuery('#coords').text(Coords.join(','));
	
	Ctx.clearRect(0, 0, Canvas.width, Canvas.height);
	Ctx.beginPath();
	
	Ctx.moveTo(Coords[0].x, Coords[0].y);
	for(var i = 1; i < Coords.length; i++) {
		Ctx.lineTo(Coords[i].x, Coords[i].y);
	}
	Ctx.lineWidth = 3;
	Ctx.fillStyle = 'rgba(255, 255, 255, 0.4)';
	Ctx.strokeStyle = 'rgba(30, 30, 30, 0.6)';
	Ctx.closePath();
	Ctx.stroke();
	Ctx.fill();
	
}

function CoordsBack() {
	if(Coords.length == 0)
		return; 
		
	Coords.pop();
	Ctx.clearRect(0, 0, Canvas.width, Canvas.height);
	Ctx.beginPath();
	
	Ctx.moveTo(Coords[0].x, Coords[0].y);
	for(var i = 1; i < Coords.length; i++) {
		Ctx.lineTo(Coords[i].x, Coords[i].y);
	}
	Ctx.lineWidth = 3;
	Ctx.fillStyle = 'rgba(255, 255, 255, 0.4)';
	Ctx.strokeStyle = 'rgba(30, 30, 30, 0.6)';
	Ctx.closePath();
	Ctx.stroke();
	Ctx.fill();
	
}

function AddArea() {
	var coordinates_to_send = [];
	
	if(Coords.length < 3) {
		alert('You need to add at least three coordinate points before saving an area! To add points, start clicking the image on left.');
		return;
	}
	
	for(var i = 0; i < Coords.length; i++)
		coordinates_to_send.push([Coords[i].x, Coords[i].y]);

	jQuery.post(ajaxurl, { 
		action: 'imgmap_save_area',
		parent_post: jQuery('#post_ID').val(),
		coords: coordinates_to_send.join(',')
	}, function(response) {
		response = JSON.parse(response);
		jQuery('#imagemap-areas > div > ul').prepend(response.html);
		jQuery('.area-list-element').change(function() { DrawSavedAreas(SavedAreasCanvas, SACtx); });
		jQuery('.delete-area').unbind('click').click(DeleteArea);
		Coords = [];
		Ctx.clearRect(0, 0, Canvas.width, Canvas.height);
		DrawSavedAreas(SavedAreasCanvas, SACtx);
	});
}

function DeleteArea() {
	if(!confirm('Do you really want to delete this area?'))
		return false;
		
	var id = jQuery(this).attr('data-area');
	var element = jQuery(this);
	jQuery.post(ajaxurl, { 
		action: 'imgmap_delete_area',
		post: id
		}, function(response) {
		response = JSON.parse(response);
		element.closest('li').remove();
		DrawSavedAreas(SavedAreasCanvas, SACtx);
	});
}

function DrawSavedAreas(canvas, ctx) {
	jQuery.post(ajaxurl, { 
		action: 'imgmap_get_area_coordinates',
		post: jQuery('#post_ID').val()
		}, function(response) {
		var areas = JSON.parse(response);
		ctx.clearRect(0, 0, canvas.width, canvas.height);
		for(var i = 0; i < areas.length; i++) {
			
			var coords = areas[i].coords.split(',');
			
			if(jQuery('#area-checkbox-' + areas[i].id).is(':checked')) {
			
				ctx.beginPath();
				ctx.moveTo(coords[0], coords[1]);
				for(var j = 0; j < coords.length; j += 2) {
					ctx.lineTo(coords[j], coords[j + 1]);
				}
				ctx.fillStyle = 'rgba(255, 0, 0, 0.8)';
				ctx.strokeStyle = 'rgba(30, 30, 30, 0.8)';
				ctx.lineWidth = 3;
				ctx.closePath();
				ctx.fill();
				ctx.stroke();
			}
		}
	});
}

function SaveTitle(id) {
	
	jQuery.post(ajaxurl, { 
		action: 'imgmap_save_area_title',
		id: id,
		title: jQuery('#'+id+'-list-area-title').val() 
		}, function(response) {
		});
}

function SetColor(id) {
	jQuery.post(ajaxurl, { 
		action: 'imgmap_set_area_color',
		color: id,
		post: jQuery('#post_ID').val(),
		title: jQuery('#'+id+'-list-area-title').val() 
		}, function(response) {
		});
}

function InitStyleEdit(dom) {
	
	if(jQuery().wpColorPicker) 
		jQuery('#imgmap-new-style-fillcolor').iris('option', 'color', '#' + jQuery(dom).attr('data-fill-color').replace('#', ''));
	else	
		jQuery('#imgmap-new-style-fillcolor').val(jQuery(dom).attr('data-fill-color').replace('#', ''));
	
	if(jQuery().wpColorPicker)
		jQuery('#imgmap-new-style-strokecolor').iris('option', 'color', '#' + jQuery(dom).attr('data-stroke-color').replace('#', ''));
	else
		jQuery('#imgmap-new-style-strokecolor').val(jQuery(dom).attr('data-stroke-color').replace('#', ''));
	
	
	jQuery('#imgmap-new-style-fillopacity').val(jQuery(dom).attr('data-fill-opacity'));
	jQuery('#imgmap-new-style-strokeopacity').val(jQuery(dom).attr('data-stroke-opacity'));
	jQuery('#imgmap-new-style-strokewidth').val(jQuery(dom).attr('data-stroke-width'));
}