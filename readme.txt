=== Soundslides ===
Contributors: wpoets
Tags: Soundslide, Soundslides
Requires at least: 3.1.0
Tested up to: 3.4.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows easier integration of a Soundslides project into your WordPress website or blog.

== Description ==

This plugin allows easier integration of a Soundslides project into your WordPress website or blog. Upload a zip file of your project and then add the project to your post as you would an image or video.

Soundslides is a software product that helps build audio slideshows from a collection of images and an audio file. Soundslides makes creating elegant slideshows a snap. Soundslides can help create very customized simple slideshows including controlled Ken Burns pans and zooms plus many other effects. The final project can be easily posted to your website or converted to video. Soundslides projects are regularly featured on the NY Times and The Guardian and many other major newspaper websites.

== Installation ==

This section describes how to install the plugin and get it working.


1. Upload all the files in `soundslide-wp` to the `/wp-content/plugins/soundslide-wp` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Upload your Soundslides project as you do with any other media
1. You can click on 'insert into post' to add the soundslide within the blog post Or,
1. You can also place the shortcode [soundslides id='<media id>' width='400px' height='300px'] directly in the blog post or page.

										Or 						

1. If you are having problems uploading your project through the media browser you can try to import larger projects by uploading via an FTP client (http://en.wikipedia.org/wiki/Comparison_of_FTP_client_software).
1. Upload your zip compressed "publish_to_web" folder via FTP to the following directory on your hosting server **/wp-content/uploads/**. 
1. After the upload is complete,goto menu 'settings/soundslides settings' Enter the name of zip file (including the zip extension) below and click attach. Your project will be available in the media library and you can then insert the project into your page or post.

== Screenshots ==

1. Soundslides project in media library.
2. Shortcode to be used.

== Changelog ==
= 1.2 =
* added the ability to attach the Soundslides project files by scanning the wp-content folder, once you have FTPed the zip file.
= 1.1 =
* added the ability to attach Soundslides project files that are uploded via FTP

= 1.0 =
* Provides basic funtionality to support soundslides projects.