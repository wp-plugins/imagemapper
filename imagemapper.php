<?php
/*
Plugin Name: ImageMapper
Plugin URI: https://github.com/SpaikFi/ImageMapper
Description: Create interactive and visual image maps with a visual editor! Based on the ImageMapster jQuery plugin.
Version: 0.5
Author: A.Sandberg AKA Spike
Author URI: http://spike.viuhka.fi
License: GPL2
*/

define('IMAGEMAP_POST_TYPE', 'imagemap');
define('IMAGEMAP_AREA_POST_TYPE', 'imagemap_area');
add_action('init', 'imgmap_create_post_type');
add_action('admin_menu', 'imgmap_custom_form');
add_action('admin_menu', 'imgmap_imagemap_tab_menu'); 
add_action('save_post', 'imgmap_save_meta');
add_action('post_edit_form_tag', 'imgmap_add_post_enctype');
add_action('template_include', 'imgmap_template');
add_action('wp_ajax_imgmap_save_area', 'imgmap_save_area_ajax');
add_action('wp_ajax_imgmap_delete_area', 'imgmap_delete_area_ajax');
add_action('wp_ajax_nopriv_imgmap_load_dialog_post', 'imgmap_load_dialog_post_ajax');
add_action('wp_ajax_imgmap_load_dialog_post', 'imgmap_load_dialog_post_ajax');
add_action('wp_ajax_imgmap_get_area_coordinates', 'imgmap_get_area_coordinates_ajax');
add_action('wp_ajax_imgmap_save_area_title', 'imgmap_save_area_title');
add_action('wp_ajax_imgmap_set_area_color', 'imgmap_set_area_color');
add_action('wp_ajax_imgmap_add_new_style', 'imgmap_add_new_style');
add_action('wp_ajax_imgmap_edit_style', 'imgmap_edit_style');
add_action('wp_ajax_imgmap_delete_style', 'imgmap_delete_style');
add_action('before_delete_post', 'imgmap_permanently_delete_imagemap');
add_action('wp_trash_post', 'imgmap_trash_imagemap');
add_action('manage_'.IMAGEMAP_POST_TYPE.'_posts_custom_column', 'imgmap_manage_imagemap_columns', 10, 2);
add_action('manage_'.IMAGEMAP_AREA_POST_TYPE.'_posts_custom_column', 'imgmap_manage_imagemap_area_columns', 10, 2);

add_filter('the_content', 'imgmap_replace_shortcode');
add_filter('post_updated_messages', 'imgmap_updated_message');
add_filter('manage_edit-'.IMAGEMAP_POST_TYPE.'_columns', 'imgmap_set_imagemap_columns');
add_filter('manage_edit-'.IMAGEMAP_AREA_POST_TYPE.'_columns', 'imgmap_set_imagemap_area_columns');
add_filter( 'manage_edit-'.IMAGEMAP_AREA_POST_TYPE.'_sortable_columns', 'imgmap_register_sortable_area_columns' );

add_filter('media_upload_tabs', 'imgmap_media_upload_tab');
add_action('media_upload_imagemap', 'imgmap_media_upload_tab_action');

$image_maps = array();


// Test data for highlight style management
$imgmap_colors = array(
	'current_id' => 12,
	'last_chosen' => 1,
	'colors' => array(
		1 => array( 'fillColor' => 'fefefe', 'strokeColor' => 'fefefe', 'fillOpacity' => 0.3, 'strokeOpacity' => 0.8, 'strokeWidth' => 2),
		2 => array( 'fillColor' => '070707', 'strokeColor' => '070707', 'fillOpacity' => 0.3, 'strokeOpacity' => 0.8, 'strokeWidth' => 2),
		3 => array( 'fillColor' => 'c94a4a', 'strokeColor' => 'e82828', 'fillOpacity' => 0.3, 'strokeOpacity' => 0.8, 'strokeWidth' => 2),
		4 => array( 'fillColor' => '1e39db', 'strokeColor' => '1e39db', 'fillOpacity' => 0.3, 'strokeOpacity' => 0.8, 'strokeWidth' => 2),
		5 => array( 'fillColor' => '1ed4db', 'strokeColor' => '1ed4db', 'fillOpacity' => 0.3, 'strokeOpacity' => 0.8, 'strokeWidth' => 2),
		6 => array( 'fillColor' => '4355c3', 'strokeColor' => '1edb4b', 'fillOpacity' => 0.3, 'strokeOpacity' => 0.8, 'strokeWidth' => 2),
		7 => array( 'fillColor' => '3ddb1e', 'strokeColor' => '3ddb1e', 'fillOpacity' => 0.3, 'strokeOpacity' => 0.8, 'strokeWidth' => 2),
		8 => array( 'fillColor' => 'dbc71e', 'strokeColor' => 'dbc71e', 'fillOpacity' => 0.3, 'strokeOpacity' => 0.8, 'strokeWidth' => 2),
		9 => array( 'fillColor' => 'db4f1e', 'strokeColor' => 'db4f1e', 'fillOpacity' => 0.3, 'strokeOpacity' => 0.8, 'strokeWidth' => 2),
		10 => array( 'fillColor' => 'd91edb', 'strokeColor' => 'd91edb', 'fillOpacity' => 0.3, 'strokeOpacity' => 0.8, 'strokeWidth' => 2),
		11 => array( 'fillColor' => '1e34db', 'strokeColor' => '1e34db', 'fillOpacity' => 0.3, 'strokeOpacity' => 0.8, 'strokeWidth' => 2),
		12 => array( 'fillColor' => 'db1e65', 'strokeColor' => 'db1e65', 'fillOpacity' => 0.3, 'strokeOpacity' => 0.8, 'strokeWidth' => 2)
		)
);

/* Creation of the custom post types 
 * Also script and stylesheet importing
 * Note: The plugin uses jQueryUI library, which includes jQuery UI Stylesheet. If you want to use your own stylesheet made with jQuery UI stylesheet generator, please replace the jquery-ui.css link address with your own stylesheet.
 * jQuery UI is only used in the dialog window which opens when user clicks a highlighted area. 
 * Later there will be option for changing the stylesheet. 
 * */
function imgmap_create_post_type() {
	if(!get_option('imgmap_colors')) {
		global $imgmap_colors;	
		add_option('imgmap_colors', $imgmap_colors);
	}
	/* Create the imagemap post type */
	register_post_type(IMAGEMAP_POST_TYPE,
		array( 
			'labels' => array(
				'name' => __('Image maps'),
				'singular_name' => __('Image map'),
				'add_new' => __('Add new Image map'),
				'all_items' => __('All Image maps'),
				'add_new_item' => __('Add new Image map'),
				'edit_item' => __('Edit Image map'),
				'new_item' => __('New Image map'),
				'view_item' => __('View Image map'),
				'search_items' => __('Search Image maps'),
				'not_found' => __('Image map not found'),
				'not_found_in_trash' => __('Image map not found in trash'),
			),
			'public' => true,
			'menu_icon' => plugins_url() . '/imagemapper/imagemap_icon.png',
			'exclude_from_search' => true,
			'has_archive' => true,
			'supports' => array(
					'title'
				)
			)
	);
	
	/* Create the imagemap area post type */
	/* Area to highlight. */
	register_post_type(IMAGEMAP_AREA_POST_TYPE,
		array( 
			'labels' => array(
				'name' => __('Image map areas'),
				'singular_name' => __('Image map area'),
				'add_new' => __('Add new Image map area'),
				'all_items' => __('All Image map areas'),
				'add_new_item' => __('Add new Image map area'),
				'edit_item' => __('Edit Image map area'),
				'new_item' => __('New Image map area'),
				'view_item' => __('View Image map area'),
				'search_items' => __('Search Image map areas'),
				'not_found' => __('Image map area not found'),
				'not_found_in_trash' => __('Image map area not found in trash'),
			),
			'public' => true,
			'has_archive' => true,
			'menu_icon' => plugins_url() . '/imagemapper/imagemap_area_icon.png',
		)
	);
	
	/* Import ImageMapster and jQuery UI */
	wp_register_script('imgmap_imagemapster', plugins_url() . '/imagemapper/script/jquery.imagemapster.min.js');
	//wp_register_style('jquery_ui', 'http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css');
	wp_register_style('imgmap_style', plugins_url().'/imagemapper/imgmap_style.css');

	/* Enqueue jQuery UI, jQuery and ImageMapster */
	wp_enqueue_style(array( 'imgmap_style' ));
	wp_enqueue_script(array( 'jquery', 'jquery-ui-core', 'jquery-ui-dialog', 'editor', 'editor_functions', 'imgmap_imagemapster' ));
	
	/* The javascript file server needs to load for plugin's functionality depends on is the page is the admin panel or a frontend page */
	/* (The frontend version obviously doesn't have for example the imagemap editor) */
	if(is_admin()) {
		wp_register_script('imgmap_admin_script', plugins_url() . '/imagemapper/imagemapper_admin_script.js');
		wp_enqueue_script(array('imgmap_admin_script'));
		
		// WP 3.5 introduced a new better color picker
		if(get_bloginfo('version') >= 3.5) {
			wp_enqueue_style(array( 'wp-color-picker' ));
			wp_enqueue_script(array( 'wp-color-picker' ));
		}
	}
	else {
		wp_register_script('imgmap_script', plugins_url() . '/imagemapper/imagemapper_script.js');
		wp_enqueue_script('imgmap_script');
	}
	
	
	wp_localize_script('imgmap_script', 'imgmap', array(
		'ajaxurl' => admin_url('admin-ajax.php')));
};

// Set custom columns for imagemap archive page
function imgmap_set_imagemap_columns($columns) {
	$new_columns['cb'] = '<input type="checkbox" />';
	$new_columns['image'] = __('Image');
	$new_columns['title'] = _x('Imagemap name', 'column name');
	$new_columns['area_count'] = __('Areas');
	$new_columns['date'] = __('Updated');
	$new_columns['author'] = __('Author');
	return $new_columns;
}

// ..and do the same for areas
function imgmap_set_imagemap_area_columns($columns) {
	$new_columns['cb'] = '<input type="checkbox" />';
	$new_columns['title'] = _x('Imagemap area name', 'column name');
	$new_columns['parent_image'] = __('Imagemap image');
	$new_columns['parent_title'] = __('Imagemap title');
	$new_columns['date'] = __('Updated');
	$new_columns['author'] = __('Author');
	return $new_columns;
}

//Define what to do for custom columns
function imgmap_manage_imagemap_columns($column_name, $id) {
	global $wpdb;
	switch($column_name) {
		case 'image':
			echo '<img class="imagemap-column-image" src="'.get_post_meta($id, 'imgmap_image', true).'" alt>';
			break;
			
		case 'area_count': 
			$areas = get_posts('post_parent='.$id.'&post_type='.IMAGEMAP_AREA_POST_TYPE.'&numberposts=-1');
			echo count($areas);
			break;
	}
}
// for the areas too
function imgmap_manage_imagemap_area_columns($column_name, $id) {
	global $wpdb;
	switch($column_name) {
		case 'parent_image':
			$post = get_post($id);
			echo '<img class="imagemap-column-image" src="'.get_post_meta($post->post_parent, 'imgmap_image', true).'" alt>';
			break;
		
		case 'parent_title':
			$post = get_post($id);
			echo '<a href="'.get_edit_post_link($post->post_parent).'">'.get_the_title($post->post_parent).'</a>';
			break;
	}
}

//Make the parent title column sortable, so there's a way to sort areas by parent image map.
function imgmap_register_sortable_area_columns( $columns ) {
	$columns['parent_title'] = 'parent_title';
	return $columns;
}

/* To enable author to upload an image for the image map. */
function imgmap_add_post_enctype() {
    echo ' enctype="multipart/form-data"';
}

/* When updating a post, Wordpress needs to check for the custom fields 
 * At the moment it's only the uploaded image.
 * */
function imgmap_save_meta($id = false) {
	
	if(get_post_type($id) == IMAGEMAP_POST_TYPE) {
		
		if(isset($_FILES['imgmap_image'])) {
			$uploadedFile = $_FILES['imgmap_image'];
			if($uploadedFile['error'] == 0){
				
				$file = wp_handle_upload($uploadedFile, array('test_form' => FALSE));
				
				if(!strpos('image/', $file['type']) == 0)
				wp_die('This is not an image!');
				
				update_post_meta($id, 'imgmap_image', $file['url']);
			}
		}
	}
	if(get_post_type($id) == IMAGEMAP_AREA_POST_TYPE) {
		$area_vars = json_decode(get_post_meta($id, 'imgmap_area_vars', true));
		$area_vars->type = $_POST['area-type'];
		$area_vars->tooltip_text = $_POST['area-tooltip-text'];
		$area_vars->link_url = esc_url($_POST['area-link-url']);
		$area_vars->title_attribute = esc_attr($_POST['area-title-attribute']);
		// Save area settings in JSON format.
		// Basically when you need one of them, you need all others as well, so it's inefficient to save them in separate columns.
		update_post_meta($id, 'imgmap_area_vars', json_encode($area_vars));
	}
}

function imgmap_updated_message( $messages ) {
	global $post_ID;
	$post = get_post($post_ID);
	if(get_post_type($post_ID) != IMAGEMAP_POST_TYPE) 
		return;
		
	$messages[IMAGEMAP_POST_TYPE] = array(
    0 => '', // Unused. Messages start at index 1.
    1 => sprintf( __('Image map updated. You can add the image map to a post with Upload/Insert media tool.') ),
    2 => __('Custom field updated.'),
    3 => __('Custom field deleted.'),
    4 => __('Image map updated.'),
    5 => isset($_GET['revision']) ? sprintf( __('Image map restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
    6 => sprintf( __('Image map published.')),
    7 => __('Image map saved.'),
    8 => sprintf( __('Image map submitted.')),
    9 => sprintf( __('Image map scheduled for: <strong>%1$s</strong>.'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
    10 => sprintf( __('Image map draft updated.')),
	);
	
	return $messages;
}

function imgmap_imagemap_tab_menu() {
	add_submenu_page('edit.php?post_type='.IMAGEMAP_AREA_POST_TYPE, 'Imagemap area styles', 'Highlight styles', 'edit_posts', basename(__FILE__), 'imgmap_area_styles');
}

/* Add custom fields to the custom post type forms. 
 * */
function imgmap_custom_form() {
	global $_wp_post_type_features;
	
	add_meta_box('imagemap-image-container', 'Image', 'imgmap_form_image', IMAGEMAP_POST_TYPE, 'normal');
	add_meta_box('imagemap-addarea', 'Add area', 'imgmap_form_addarea', IMAGEMAP_POST_TYPE, 'side');
	add_meta_box('imagemap-areas', 'Areas', 'imgmap_form_areas', IMAGEMAP_POST_TYPE, 'side');
	
	remove_post_type_support(IMAGEMAP_AREA_POST_TYPE, 'editor');
		
	add_meta_box('imagemap-area-highlight', 'Highlight', 'imgmap_area_form_highlight', IMAGEMAP_AREA_POST_TYPE, 'side');
	add_meta_box('imagemap-area-settings', 'Settings', 'imgmap_area_form_settings', IMAGEMAP_AREA_POST_TYPE, 'side');
	add_meta_box('imagemap-area-types', 'Click event', 'imgmap_area_form_types', IMAGEMAP_AREA_POST_TYPE, 'normal');
}

/* Custom field for the imagemap image.
 * Includes also the imagemap editor.
 *  */
function imgmap_form_image($post) {
	?>
	<input type="file" name="imgmap_image" id="file" />
	<div style="position: relative; margin-top: 30px">
		<img src="<?php echo get_post_meta($post->ID, 'imgmap_image', true); ?>" usemap="#imgmap-<?php echo $post->ID ?>" id="imagemap-image" />
		<canvas id="image-area-canvas"></canvas>
		<canvas id="image-coord-canvas"></canvas>
	</div>
	<?php
		
		$areas = get_posts('post_parent='.$post->ID.'&post_type='.IMAGEMAP_AREA_POST_TYPE.'&numberposts=-1');
		
	?>
	<map name="imgmap-<?php echo $post->ID ?>">
		<?php
			foreach($areas as $a) {
				echo imgmap_create_area_element($a->ID, $a->post_title);
			}
		?>
	</map>
	<?php
}

function imgmap_media_upload_tab($tabs) {
	$newtab = array('imagemap' => __('Image map', 'imagemap'));
	return array_merge($tabs, $newtab);
}

function imgmap_media_upload_tab_action() {
	return wp_iframe('media_imgmap_media_upload_tab_inside');
}

function media_imgmap_media_upload_tab_inside() {
	media_upload_header(); ?>
	<p>
		<?php
		$areas = get_posts('post_type='.IMAGEMAP_POST_TYPE.'&numberposts=-1');
		foreach($areas as $a) { ?>
			<div data-imagemap="<?php echo $a->ID; ?>" class="insert-media-imagemap" style="background-image: url(<?php echo get_post_meta($a->ID, 'imgmap_image', true); ?>);">
				<div><?php echo $a->post_title ?></div>
			</div>
		<?php }
		?>
	</p>
	<?php
}

/* Displays the image map in a frontend page. */
function imgmap_frontend_image($id, $element_id) {
	echo get_imgmap_frontend_image($id, $element_id);
}
	
function get_imgmap_frontend_image($id, $element_id) {
	$areas = array();
	$value = '
	<div class="imgmap-frontend-image">
	<div class="imgmap-dialog-wrapper" id="imgmap-dialog-'.$element_id.'"></div>
	<img src="'.get_post_meta($id, 'imgmap_image', true).'" usemap="#imgmap-'.$element_id.'" id="imagemap-'.$element_id.'" />
	<map name="imgmap-'.$element_id.'">';
	$areas = get_posts('post_parent='.$id.'&post_type='.IMAGEMAP_AREA_POST_TYPE.'&numberposts=-1');
	foreach($areas as $a) {
		$value .= imgmap_create_area_element($a->ID, $a->post_title);
	}
	$value .= '</map>';
	
	//foreach($areas as $a) {
	//	$value .= '<a href="#">Post: '.$a->post_title.'</a>';
	//}
	$value .= '
	</div>';
	return $value;
}

/* Fields for adding new areas to the imagemap using the editor.
 * However the editor functionality is included in the image field. */
function imgmap_form_addarea($post) {
	?><h4><?php _e('Instructions for area shaping'); ?></h4>
	<p><?php _e('Start creating the shape of the new area by clicking the image of the image map on the left.'); ?></p>
	<p><?php _e('The first and the last point of the path are joined automatically'); ?></p>
	<p><?php _e('You can use Shift + Mouse left button to go back.');?></p>
	<p><?php _e('When the shape is ready, press the button below.');?></p>
	<input type="button" value="Add area" id="add-area-button"/>
	<?php
}

/* List of the current areas of the imagemap. 
 * Every element in the list has link to edit form of the area and a shortcut for deleting the areas. */
function imgmap_form_areas($post) {
	$areas = get_posts('post_parent='.$post->ID.'&post_type='.IMAGEMAP_AREA_POST_TYPE.'&orderby=id&order=desc&numberposts=-1');
	echo '<ul>';
	foreach($areas as $a) {
		echo imgmap_create_list_element($a->ID);
	}
	echo '</ul>';
}

/* Settings for the single imagemap area */
function imgmap_area_form_settings($post) { 
$meta = json_decode(get_post_meta($post->ID, 'imgmap_area_vars', true)); 
$meta->title_attribute = isset($meta->title_attribute) ? $meta->title_attribute : '';
?>
<p><input style="width: 100%" type="text" name="area-title-attribute" value="<?php echo $meta->title_attribute; ?>" placeholder="HTML title attribute"></p>
<p><a title="The HTML title attribute often shows as a small tooltip when mouse hovers over an element.">What is this?<br>Hover mouse over this text for an example.</a></p>
<?php
}

/* Settings for the single imagemap area highlight */
function imgmap_area_form_highlight($post) {
	$imgmap_colors = get_option('imgmap_colors');
	$meta = json_decode(get_post_meta($post->ID, 'imgmap_area_vars', true));
	?> 
	<div id="imgmap-area-styles"><?php
	foreach($imgmap_colors['colors'] as $key => $color) { 
		echo imgmap_get_style_element($key, $color, $meta->color);
	}
	if(count($imgmap_colors['colors']) == 0) 
		echo '<p>'.__('No styles found. Start by adding a new style.').'</p>';
	?><br style="clear:both;"></div>
	<a style="display: block; padding: 8px;" href="edit.php?post_type=imagemap_area&page=imagemapper.php">Add new</a><?php
}

function imgmap_get_style_element($key, $color, $chosen = false, $data = false) { 
	echo '
	<div class="imgmap-area-style'.($key == $chosen ? ' chosen' : '').'" data-id="'.$key.'" title="'.$key.'"
	'.($data ? '
	data-fill-color="'.esc_attr($color['fillColor']).'"
	data-fill-opacity="'.esc_attr($color['fillOpacity']).'"
	data-stroke-color="'.esc_attr($color['strokeColor']).'"
	data-stroke-opacity="'.esc_attr($color['strokeOpacity']).'"
	data-stroke-width="'.esc_attr($color['strokeWidth']).'"' :
	'').'>
		<div class="imgmap-area-color" style="
		background-color: '.imgmap_hex_to_rgba($color['fillColor'], $color['fillOpacity']).';
		box-shadow: 0 0 0 '.$color['strokeWidth'].'px '.imgmap_hex_to_rgba($color['strokeColor'], $color['strokeOpacity']).'"
		></div>
	</div>';
}

function imgmap_area_styles() { ?>
	<div class="wrap">
	<h2>Imagemap highlight styles</h2>
	<div class="divide-left">
		<h3>Saved styles</h3>
		<?php
		$imgmap_styles = get_option('imgmap_colors');
		?>
			<div id="imgmap-area-styles-edit"><?php 
				if(count($imgmap_styles['colors']) == 0) 
					echo '<p>'.__('No styles found. Start by adding a new style.').'</p>';
			
				foreach($imgmap_styles['colors'] as $key => $color) {
					echo imgmap_get_style_element($key, $color, false, true); }
				?>
		</div><br style="clear:both;">
	</div>
	<div class="divide-right">
	<h3>Add/Edit styles</h3>
		<div id="add-new-imgmap-style">
			<table>
			<tr>
				<th>Fill color</th>
				<th>Fill opacity</th>
			</tr>
			<tr>
				<td>
					<div class="form-field">
						<input type="text" maxlength="6" id="imgmap-new-style-fillcolor" class="color-picker-field" />
					</div>
				</td>
				<td>
					<div class="form-field">
						<input type="number" value="1" min="0" max="1" step="0.1" id="imgmap-new-style-fillopacity" />
					</div>
				</td>
			</tr>
			<tr>
				<th>Stroke color</th>
				<th>Stroke opacity</th>
				<th>Stroke width</th>
			</tr>
			<tr>
				<td>
					<div class="form-field">
						<input type="text" maxlength="6" id="imgmap-new-style-strokecolor" class="color-picker-field" />
					</div>
				</td>
				<td>
					<div class="form-field">
						<input type="number" value="1" min="0" max="1" step="0.1" id="imgmap-new-style-strokeopacity" />

					</div>
				</td>
				<td>
					<div class="form-field">
						<input type="number" value="1" min="0" step="1" id="imgmap-new-style-strokewidth" />
					</div>
				</td>
			</tr>
		</table>
			<p>
				<input type="button" class="button" id="add-new-imgmap-style-button" value="Add new style"> 
				<input type="button" class="button" id="edit-imgmap-style-button" value="Save changes" disabled>
				<input type="button" class="button" id="delete-imgmap-style-button" value="Delete style" disabled>
			</p>
		</div>
	</div>
	<?php
}

function imgmap_area_form_types($post) { 
	// Get area variables from post meta 
	$meta = json_decode(get_post_meta($post->ID, 'imgmap_area_vars', true));
	$meta->type = isset($meta->type) ? $meta->type : '';
	$meta->tooltip_text = isset($meta->tooltip_text) ? $meta->tooltip_text : '';
	$meta->link_url = isset($meta->link_url) ? $meta->link_url : '';
	?>
	<div style="width: 20%; float: left;" id="area-form-types">
		<p><input type="radio" name="area-type" onclick="ShowTypes('link')" value="link" <?php echo $meta->type == 'link' ? 'checked' : '' ?>> 
			<input type="button" class="button" onclick="ShowTypes('link')" value="Link" /></p>
		<p><input type="radio" name="area-type" onclick="ShowTypes('tooltip')" value="tooltip" <?php echo $meta->type == 'tooltip' ? 'checked' : '' ?>> 
			<input type="button" class="button" onclick="ShowTypes('tooltip')" value="Tooltip" /></p>
		<p><input type="radio" name="area-type" onclick="ShowTypes('popup')" value="popup" <?php echo $meta->type == 'popup' ? 'checked' : '' ?>> 
			<input type="button" class="button" onclick="ShowTypes('popup')" value="Popup window" /></p>
	</div>
	<div style="width: 75%; float: right;">
		<div id="imagemap-area-popup-editor" class="area-type-editors <?php echo $meta->type == 'popup' ? 'area-type-editor-current' : '' ?>">
		<?php 
		if(function_exists('wp_editor')) {
			wp_editor($post->post_content, 'content', array( 'editor_css' => '<style> body { min-height: 300px; background-color: white; } </style>' )); 
		}
		else if(function_exists('the_editor')) {
			the_editor($post->post_content, 'content', array( 'textarea_rows' => 8 ));
		}
		else {
			echo 'Something went wrong when loading editor.';
		}
		?></div>
		<div id="imagemap-area-tooltip-editor" class="area-type-editors <?php echo $meta->type == 'tooltip' ? 'area-type-editor-current' : '' ?>">
			<p><label>Tooltip text <br />
				<textarea cols="60" rows="8" name="area-tooltip-text"><?php echo $meta->tooltip_text ?></textarea>
			</label></p>
		</div>
		<div id="imagemap-area-link-editor" class="area-type-editors <?php echo $meta->type == 'link' ? 'area-type-editor-current' : '' ?>">
			<p><label>Url address: <br /><input type="text" name="area-link-url" value="<?php echo $meta->link_url; ?>"></label></p>
		</div>
	</div>
	<br style="clear:both">
<?php }

/* Used when user adds a new area to the image map 
 * The function returns object with data of the newly-added area and link to edit it. 
 * Currently Wordpress should be redirecting user to the area edit form after the area has been saved. 
 * However there's a bug with the redirecting and it's redirecting in wrong page. Might be that Wordpress doesn't allow the redirect. */
function imgmap_save_area_ajax() {
	global $wpdb;
	$area = new StdClass();
	$area->coords = $_POST['coords'];
	$area->text = '';
	$area->title = 'New image map area'; 
	$area->title_attribute = '';
	$area->parent = $_POST['parent_post'];
	$post = array(
	'post_author'    => get_current_user_id(),
	'post_content'   => $area->text,
	'post_parent'    => $area->parent,
	'post_status'    => 'publish',
	'post_name' 	 => $area->title,
	'post_title'     => $area->title,
	'post_type'      => IMAGEMAP_AREA_POST_TYPE
	);
	$post = wp_insert_post($post);
	
	$area->id = $post;
	$area->link = get_edit_post_link($area->id);
	update_post_meta($area->id, 'coords', $area->coords);
	$meta = new StdClass();
	
	$meta->color = $styles['last_chosen'];
	update_post_meta($area->id, 'imgmap_area_vars', json_encode($meta));
	$area->html = imgmap_create_list_element($area->id);
	ob_clean();
	echo json_encode($area);
	die();
}

/* Shortlink for deleting an area. (Well, the functionality which happens when the shortlink is pressed. */
function imgmap_delete_area_ajax() {
	echo json_encode(wp_delete_post($_POST['post'], true));
	die();
}

/* Creates an area element to the HTML image map */
function imgmap_create_area_element($id, $title) {	
	$imgmap_colors = get_option('imgmap_colors');
	$meta = json_decode(get_post_meta($id, 'imgmap_area_vars', true));
	
	if($meta === null)
		$meta = new StdClass();
	
	if(!isset($meta->color) || !isset($imgmap_colors['colors'][$meta->color]))
		$meta->color = $imgmap_colors['last_chosen'];
		
	if(!isset($imgmap_colors['colors'][$meta->color])) 
		$color = array( 'fillColor' => 'fefefe', 'strokeColor' => 'fefefe', 'fillOpacity' => 0.3, 'strokeOpacity' => 0.6, 'strokeWidth' => 1);
	else
		$color = $imgmap_colors['colors'][$meta->color];
	
	$meta->type = isset($meta->type) ? $meta->type : '';
	$meta->tooltip_text = isset($meta->tooltip_text) ? $meta->tooltip_text : '';
	$meta->link_url = isset($meta->link_url) ? $meta->link_url : '';
	$meta->title_attribute = isset($meta->title_attribute) ? $meta->title_attribute : '';
	
	return '
	<area
	data-type="'.esc_attr($meta->type).'"
	data-tooltip="'.esc_attr($meta->type == 'tooltip' ? $meta->tooltip_text : false ). '"
	data-fill-color="'.esc_attr(str_replace('#', '', $color['fillColor'])).'"
	data-fill-opacity="'.esc_attr($color['fillOpacity']).'"
	data-stroke-color="'.esc_attr(str_replace('#', '', $color['strokeColor'])).'"
	data-stroke-opacity="'.esc_attr($color['strokeOpacity']).'"
	data-stroke-width="'.esc_attr($color['strokeWidth']).'"
	data-mapkey="area-'.$id.'" 
	shape="poly" coords="'.esc_attr(get_post_meta($id, 'coords', true)).'" 
	href="'.esc_attr($meta->type == 'link' ? $meta->link_url : '#') .'"
	title="'.(isset($meta->title_attribute) ? $meta->title_attribute : $title).'" />';
}

/* Creates an list element to the list of imagemap's areas. */
function imgmap_create_list_element($id) {
	return 
	'<li data-listkey="area-'.$id.'" class="area-list-element">
	<div class="area-list-left">
		<input id="area-checkbox-'.$id.'" data-listkey="area-'.$id.'" type="checkbox" checked>
	</div>
	<div class="area-list-right">
		<label>Title: <input type="text" id="'.$id.'-list-area-title" value="'.get_the_title($id).'" /><div style="clear: both"></div></label>
		<div class="area-list-meta">
			<a class="save-area-link" href="#" onclick="SaveTitle('.$id.')">Save</a>
			<a class="edit-area-link" href="'.get_edit_post_link($id).'">Edit page</a>
			<a class="delete-area" data-area="'.$id.'">Delete</a>
		</div>
	</div>
	</li>';
}

/* Template for the imagemap frontend page. 
 * Checks first the theme folder. 
 * Note: If you want to edit the image map template, please check the single_imgmap.php template file in plugin's directory. */
function imgmap_template($template) {
	$post = get_the_ID();
	if(get_post_type() == IMAGEMAP_POST_TYPE) {
		if(locate_template(array('single-imgmap.php')) != '') 
			include locate_template(array('single-imgmap.php'));
		else
			include 'single-imgmap.php';
		return;
	}
	return $template;
}

/* Loads post in a jQuery dialog when a highlighted area is clicked. 
 * Checks first the theme folder, too */
function imgmap_load_dialog_post_ajax() {
	$post = get_post($_POST['id']);
	if(locate_template(array('single-imgmap-dialog.php')) != '') 
		include locate_template(array('single-imgmap-dialog.php'));
	else
		include 'single-imgmap-dialog.php';
	die();
}

/* Returns array of area data of an imagemap. */
function imgmap_get_area_coordinates_ajax() {
	$return = array();
	$areas = get_posts('post_parent='.$_POST['post'].'&post_type='.IMAGEMAP_AREA_POST_TYPE.'&orderby=id&order=desc&numberposts=-1');
	foreach($areas as $a) {
		$newArea = new StdClass();
		$newArea->coords = get_post_meta($a->ID, 'coords', true);
		$newArea->id = $a->ID;
		$return[] = $newArea;
	}
	echo json_encode($return);
	die();
}

/* Be sure to delete areas when deleting parent post */
function imgmap_permanently_delete_imagemap($post_id) {
	imgmap_delete_imagemap($post_id, true);
}

/* ...and be sure to trash areas when trashing parent post as well. */
function imgmap_trash_imagemap($post_id) {
	imgmap_delete_imagemap($post_id, false);
}

/* Delete areas when deleting imagemap. 
 * Doesn't actually restore trashed imagemap areas when restoring the imagemap. */
function imgmap_delete_imagemap($post_id, $permanent) {
	
	$args = array( 
    'post_parent' => $post_id,
    'post_type' => IMAGEMAP_POST_TYPE
	);
	
	$posts = get_posts( $args );
	
	if (is_array($posts) && count($posts) > 0) {
		// Delete all the Children of the Parent Page
		foreach($posts as $post){
			wp_delete_post($post->ID, $permanent);
		}
	}
}

/* Insert image map code in posts */
function imgmap_replace_shortcode($content) {
	global $imagemaps;
	preg_match_all('/\[imagemap id=\"(.*?)\"\]/', $content, $maps);
	foreach($maps[1] as $map) {
		if(!isset($imagemaps[$map]))
			$imagemaps[$map] = 0;
		$imagemaps[$map]++;
			
		$content = preg_replace('/\[imagemap id=\"'.$map.'\"\]/', get_imgmap_frontend_image($map, $map.'-'.$imagemaps[$map]), $content, 1);
	}
	return $content;
}

function imgmap_save_area_title() {
	if(current_user_can('manage_options')) {
		$id = $_POST['id'];
		$post = get_post($id);
		$post->post_title = $_POST['title'];
		echo wp_update_post($post);
	}
	die();
}

function imgmap_set_area_color() {
	if(current_user_can('manage_options')) {
		$id = $_POST['post'];
		$color = $_POST['color'];
		$meta = json_decode(get_post_meta($id, 'imgmap_area_vars', true));
		echo json_encode($meta);
		$meta->color = $color;
		update_post_meta($id, 'imgmap_area_vars', json_encode($meta));
	}
	die();
}

function imgmap_add_new_style() {
	if(current_user_can('manage_options')) {
		if(substr($_POST['fillColor'], 0, 1) == '#')
			$_POST['fillColor'] = substr($_POST['fillColor'], 1);

		$style = array(
			'fillColor' => $_POST['fillColor'],
			'fillOpacity' => $_POST['fillOpacity'],
			'strokeColor' => $_POST['strokeColor'],
			'strokeOpacity' => $_POST['strokeOpacity'],
			'strokeWidth' => $_POST['strokeWidth']
		);
		$style_option = get_option('imgmap_colors');
		$key = $style_option['current_id'] + 1;
		$style_option['colors'][$key] = $style;
		$style_option['current_id']++;
		
		update_option('imgmap_colors', $style_option);
		echo imgmap_get_style_element($key, $style, true, true);
	}
	die();
}

function imgmap_edit_style() {
	if(current_user_can('manage_options')) {
		$style = array(
			'fillColor' => $_POST['fillColor'],
			'fillOpacity' => $_POST['fillOpacity'],
			'strokeColor' => $_POST['strokeColor'],
			'strokeOpacity' => $_POST['strokeOpacity'],
			'strokeWidth' => $_POST['strokeWidth']
		);
		$id = $_POST['id'];
		$style_option = get_option('imgmap_colors');
		$style_option['colors'][$id] = $style;
		
		update_option('imgmap_colors', $style_option);
		echo imgmap_get_style_element($id, $style, true, true);
	}
	die();
}

function imgmap_delete_style() {
	if(current_user_can('manage_options')) {
		$style_option = get_option('imgmap_colors');
		$id = $_POST['id'];
		unset($style_option['colors'][$id]);
		update_option('imgmap_colors', $style_option);
	}
	die();
}

function imgmap_hex_to_rgba($hex, $opacity = false) {
	
	if(substr($hex, 0, 1) == '#')
		$hex = substr($hex, 1);
		
	$red = substr($hex, 0, 2);
	$green = substr($hex, 2, 2);
	$blue = substr($hex, 4, 2);
	
	$red = hexdec($red);
	$green = hexdec($green);
	$blue = hexdec($blue);
	
	if(is_numeric($opacity))
		return 'rgba('.$red.', '.$green.', '.$blue.', '.$opacity.')';
	else
		return 'rgb('.$red.', '.$green.', '.$blue.')';
}

?>