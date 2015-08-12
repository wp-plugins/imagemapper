=== ImageMapper ===
Contributors: spikefinned, tatti
Tags: image map, imagemap, images, interactive images
Requires at least: 3.3.1
Tested up to: 4.3
Stable tag: 1.2.6
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create interactive and visual image maps with a visual editor.

== Description ==

ImageMapper is a WordPress plugin designed to add interactivity in images in posts. The plugin was originally designed for web comics, but with its good customization options it can be used for adding interactivity to any kind of images. 

Redirecting user to a different page when clicking certain character in a web comic? Displaying a person's name and home page link in a group photo when mouse is hovering over them? Showing additional info and notes in a large graphs? Possible.

Based on the ImageMapster jQuery plugin. [ImageMapster homepage](http://www.outsharked.com/imagemapster/).

The map of Finland in the banner image is provided by National Land Survey of Finland.

Instructions for use are found in Installation page.

About support: This plugin was built during a project that has ended in 2013. Further development resources are very limited. If you need something fixed, most likely you'll need to fix it yourself. However, any patches are welcome and can be added to the main code base. If someone wants to become a co-author on this plugin, let us know.

== Installation ==

1. Upload imagemapper folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

How to create a new Image map:

1. Choose Image maps from the left of Wordpress admin panel and create a new image map.
1. Choose the image file and save the post to upload it.
1. To add new areas to the image, start creating the path by clicking the image. When the path looks good press Add area. The area will appear in the area list on the image map editing page.
1. You can edit the content of the area by clicking Edit page from the area list.

How to insert image map to a post:

1. Adding image map to the post is simple. After you have created the image map, go to the post page. 
1. Click Add Media (or Insert/Upload media) and choose Image map tab. 
1. Click the image map you want to insert into the post.

== Changelog ==

= 1.2.6 =
* Fixed imagemap calls so that both shortcodes and template embeds work correctly. Kudos to edanwp!

= 1.2.5 =
* Fixed CSS so imagemaps work on responsive sites. Kudos to Pourgt!

= 1.2.4 =
* Fixed to work with Wordpress 4.0.1. Kudos to Samatva!

= 1.2.3 =
* Imagemap areas no longer show up in search results.
* Minor fixes to visual editor problems.

= 1.2.2 =
* Bug fixes
* Added new popup layout. Can be enabled/disabled from Imagemap Settings. Mainly an alternative option if the old layout doesn't work well.

= 1.2.1 = 
* Fluid width popup window. 

= 1.2 =
* Support for Wordpress internal linking.
* Highlight in the area editor now corresponds the style setting of each area.
* A few GUI tweaks.

= 1.1.1 =
* Small bug fixes focusing on conflicts with other plugins.

= 1.1 =
* Popup windows can now be closed by clicking outside the window.
* Admins can now choose if they want to show all the image map areas for a short time when user moves the mouse on the image (for the first time or always). This helps users to realize that they can get additional information about the image by searching for highlights.
* Minor bug fixes.

= 1.0 =
* Fixed a bug with scandinavian letters in tooltips.
* Added a fallback links setting for image maps. Admins can choose if they want to show links corresponding the areas of the image map below the image to the user.
* Disabled scrolling effect in Image map editor page. 

= 0.5 = 
* Fixed bug where a highlight's fill opacity affected to the stroke opacity in style preview. 
* Added color picker support for highlight fill color and stroke color fields. (Using the Iris color picker introduced in WP 3.5)
* Changed popup dialog layout to match tooltip layout.
* HTML title attribute can now be edited separately from the post title. (The attribute shows as a small tooltip when hovering over an area and might be distracting if there's already a tooltip.)
* Highlight styles can now be edited and deleted.

= 0.4 =
* Added menu icons for Image maps and areas.
* Different styles for area highlights including fill color with opacity and stroke with color, opacity and width.
* Adding custom styles for highlights area also possible.

= 0.3 =
* Fixed a bug which prevented inserting image map to the post with Insert media window in WordPress 3.5
* Images of image maps in archive pages.
* Click events: Possibility to choose if an area acts as a regular link, shows a tooltip when hovering or opens up a post content in a dialog.
* Prevent adding an empty area or area with only two points.

= 0.2 =
* Support for adding image maps in posts.
* Support for multiple image maps.

= 0.1 =
* First release.
