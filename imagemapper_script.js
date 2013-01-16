/* ImageMapper Wordpress frontend script
*/
var Image;
var Canvas, Ctx;

jQuery(function($) {
	$('img[usemap]').each(function() {
		var areas = [];
		$('map[name="' + $(this).attr('usemap').substr(1) + '"]').find('area').each(function() {
			areas.push({
				'key': $(this).attr('data-mapkey'),
				'toolTip': $(this).attr('data-tooltip'),
				'isSelectable': false,
				'render_highlight': {
					'fillColor': $(this).attr('data-fill-color'),
					'fillOpacity': $(this).attr('data-fill-opacity'),
					'strokeColor': $(this).attr('data-stroke-color'),
					'strokeOpacity': $(this).attr('data-stroke-opacity'),
					'stroke': $(this).attr('data-stroke-width') > 0,
					'strokeWidth': $(this).attr('data-stroke-width')
				}
			});
		});
		// console.log(areas);
		$(this).mapster({
			clickNavigate: true,
			showToolTip: true,
			toolTipContainer: $('<div class="imagemapper-tooltip"></div>'),
			toolTipClose: ['area-click', 'tooltip-click'],
			mouseoutDelay: 800,
			mapKey: 'data-mapkey',
			onClick: AreaClicked,
			singleSelect: true,
			render_select: {
				fillOpacity: 0
			},
			areas: areas
		});
			
	});
	
	$('.imgmap-dialog-wrapper').dialog({ 
		autoOpen: false, 
		zIndex: 10000,
		width: 700,
		show: 300,
		dialogClass: 'imgmap-dialog',
		position: {
			of: $(parent)
			}
		});
	$('.mapster_el').load(function() {
	});
	
	$('.alternative-links-imagemap').
	click(AlternativeLinkClicked).
	mouseenter(function () {
		var mapster = $($(this).attr('data-parent').replace('imgmap', '#imagemap')).get(0);
		jQuery(mapster).mapster('highlight', false);
		jQuery(mapster).mapster('highlight', $(this).attr('data-key'));
	}).
	mouseleave(function () {
		var mapster = $($(this).attr('data-parent').replace('imgmap', '#imagemap')).get(0);
		jQuery(mapster).mapster('highlight', false);
	});
	
	$('.altlinks-toggle').click(function() {
		$('#altlinks-container-' + $(this).attr('data-parent')).toggle(200);
	});
	
});



function AlternativeLinkClicked() {
	var key = jQuery(this).attr('data-key');
	var type = jQuery(this).attr('data-type');
	var parent = jQuery(this).attr('data-parent');
	AlternativeLinkAction(key, type, parent);
}

function AlternativeLinkAction(areaKey, areaType, imgmap) {
	switch(areaType) {
		case 'popup': 
			OpenImgmapDialog(areaKey, jQuery('map[name="' + imgmap + '"]').get(0));
		break;
		
		case 'tooltip':
			imgmap = imgmap.replace('imgmap', '#imagemap');
			jQuery(imgmap).mapster('tooltip', areaKey);
		break;
	}
}

function AreaClicked(data) {
	var type = jQuery('area[data-mapKey='+data.key+']').attr('data-type'); 
	// console.log(type);
	if(type == 'popup' || type == '' ) {
		OpenImgmapDialog(data.key, jQuery(this).parent()[0]);
	}
}

function OpenImgmapDialog(key, parent) {
	console.log(key + ', ' + parent);
	var dialog = parent.name.replace('imgmap', '#imgmap-dialog');
		jQuery(dialog).dialog('option', 'title', jQuery('area[data-mapkey='+key+']').attr('title'));
		jQuery.post(imgmap.ajaxurl, { 
			action: 'imgmap_load_dialog_post',
			id: key.replace('area-', '')
			}, function(response) {
			jQuery(dialog).html(response).dialog('open');
		});
}

