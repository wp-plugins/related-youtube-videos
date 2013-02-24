=== Related YouTube Videos ===

Contributors:       Zenation
Donate link:        https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5K6UDDJRNKXE2
Tags:               videos, youtube, related
Requires at least:  3.0.0
Tested up to:       3.5.1
Stable tag:         1.0.3
License:            GPLv2
License URI:        http://www.gnu.org/licenses/gpl-2.0.html

Automatically embed YouTube videos that are related to your content.

== Description ==

**relatedYouTubeVideos** is a free WordPress plugin that embeds a number of, well, YouTube videos that are related to your content. The list is put together by using the YouTube search API. And you can specify the relation between the videos and your content by:

* the title of your current post, page or custom post type.
* the tags of your current post, page or custom post type.
* any keywords you specify.

Also, this plugin offers you two ways to embed related videos:

1. by using the shortcode [relatedYouTubeVideos] somewhere in your post/page content
2. by using the WordPress widget "Related YouTube Videos" in any of your widget areas (multiple instances are possible)

= The Shortcode =

You can put the shortcode **[relatedYouTubeVideos]** anywhere you want inside the content of a page, post, or custom post type - as long as your WordPress theme is supporting shortcodes.

You can also add a number of attributes to configure the assembling of the list of videos that will be embedded:

* 'width'       (numeric)   Width of the HTML video object
* 'height'      (numeric)   Height of the HTML video object
* 'terms'       (string)    Search YouTube for these terms - no separating commas required.
* 'orderBy'     (string)    Can either be 'published', 'rating', 'viewCount', (default) 'relevance'.
* 'start'       (numeric)   Offset / numbers of search results that will be skipped. 0 being the default.
* 'max'         (numeric)   Number of videos (or search results) that will be returned. Can be any number between 1 and 10!
* 'apiVersion'  (numeric)   Version of the YouTube/Google API that will be used.
* 'class'       (string)    You can specify an additional HTML class name for the wrapping `<ul>` element
* 'id'          (string)    You can specify the HTML id attribute for the wrapping `<ul>` element.
* 'relation'    (string)    Specify the kind of relation that shall be used for searching YouTube. Can either be 'postTitle', 'postTags', or 'keywords' (in which case the attribute 'terms' will be used for the YouTube search).
  
I recommend always using the attributes 'relation', 'max', and if the relation shall be 'keywords' the 'terms' attribute. Depending on your design you might also set a custom width and height for the videos so they fit in properly.

Shortcode Example 1: **[relatedYouTubeVideos relation="postTags" max="3"]** Will show three videos coming back from the search YouTube for (all!) the tags you have assigned to this post or page.

Shortcode Example 2: **[relatedYouTubeVideos relation="keywords" terms="monty python" max="5"]** Will show five Monty Python videos from YouTube.

Shortcode Example 3: **[relatedYouTubeVideos relation="postTitle" max="1" orderBy="viewCount" start="1"]** Will show the second most popular video (the first being skipped) relating to your post or page title.

= The Widget =

The widget almost works the same way. Or at least it has the same options for configuring the video request. If you log into your WordPress backend and go to the "Appearance > Widget" menu (given that your theme supports widgets) you can drag&drop a widget instance into the widget area of your choice.

The *relatedYouTubeVideos* widget allows multiple instances. So you can put as many widgets as you like into as many widget areas as you like.

The difference between the widget and the shortcode is not in terms of functionality but usually in the context they reside. Widgets usually go into sidebars or footers and alike and the same widget usually shows up for many, if not all pages, just the same. Shortcodes are placed inside the actual content and therefore will only show up when the page or post where they're put is shown.

= Errors =

The videos will be embedded from YouTube by using its search API. This API call is being done by sending an internal request over the web. And as we all know, there can be a million reasons why even a service like YouTube cannot be reached all the time. If that's the case or a request itself is invalid for some reason you will get an error message and no videos! But instead of breaking your design by showing your visitors error messages, they will just see nothing: The error message will be hidden in the HTML source code in form of an HTML comment.

So if you don't see any videos while you think you should, please take a look at the HTML source code of your current page and look for "[relatedYouTubeVideos] Error...".

== Installation ==

Basically, there are three ways to install this or any other WordPress plugin.

= The WordPress Plugin Installer =

Log into the WordPress backend as someone who has the right to install and activate new plugins. Go the "Plugins" section and click the "Add New" button at the top of the page. Enter "relatedYouTubeVideos" into the search field and hit the "Search Plugins" button. Look out for this plugin and click the "install now" link under the plugin name in the first column. After the plugin has been installed automatically you only have to click the "Activate Plugin" link and you're good to go.

= Uploading The ZIP file Yourself =

You can also download the current plugin ZIP file, for example from [http://www.WordPress.org/extend/plugins/related-youtube-videos/](http://www.WordPress.org/extend/plugins/related-youtube-videos/) and upload it yourself.

Just log into the WordPress backend as someone who has the right to install and activate new plugins. Go to the "Plugins" section and click the "Upload" link. Select the ZIP file from your local machine and hit the "Install Now" button. After the plugin has been installed you only have to click the "Activate Plugin" link and you're good to go.

= Upload via FTP =

You can also download the current plugin ZIP file, for example from [http://www.WordPress.org/extend/plugins/related-youtube-videos/](http://www.WordPress.org/extend/plugins/related-youtube-videos/), and upload it yourself via FTP, for example.

First you have to download the plugin ZIP file, for example from [http://www.WordPress.org/extend/plugins/related-youtube-videos/](http://www.WordPress.org/extend/plugins/related-youtube-videos/), and extract the archive on your local machine. Then start any FTP client of your choice (e.g. FileZilla) and connect to your web server. Browse to your WordPress' root directory and then into /wp-content/plugins/. Now upload the /relatedYouTubeVideos/ folder you just extracted from the ZIP archive with all its containing files.

Then log into the WordPress backend as someone who has the right to install and activate new plugins. Go to the "Plugins" section and look for the "relatedYouTubeVideos" in your plugin list. If the plugin doesn't appear in this list, you might have to switch to the "Inactive" tab and look again. If you have found it just hit the "Activate" link below the plugin name in the first column and you are good to go.

== Other Notes ==

Developers can also use the API class outside the plugin context, for example in a theme template file. All you have to do is include the class (if it doesn't already exist) and create an object like this:
`
$RYV  = new RelatedYouTubeVideos();

// Configuring the request
$args = $RYV->validateConfiguration(
  array(
    'width'       => 720,         // (numeric)  Width of the HTML video object
    'height'      => 480,         // (numeric)  Height of the HTML video object
    'orderBy'     => 'relevance', // (string)   Can either be 'published', 'rating', 'viewCount', (default) 'relevance'.
    'start'       => 0,           // (numeric)  Offset / numbers of search results that will be skipped - could in theory be used for pagination.
    'max'         => 3,           // (numeric)  Number of videos (or search results) that will be returned. Can be any number between 1 and 10!
    'apiVersion'  => 2,           // (numeric)  Version of the YouTube/Google API that will be used.
    'class'       => ''           // (string)   You can specify an additional HTML class name for the wrapping <ul> element
    'id'          => ''           // (string)   You can specify the HTML id attribute for the wrapping <ul> element.
    'relation'    => 'postTags',  // (string)   Specify the kind of relation that shall be used for searching YouTube. Can either be 'postTile', 'postTags', or 'keywords' (in which case the attribute 'keywords' will be used).
    'terms'       => ''           // (string)   Search YouTube for these terms
  )
);

// Getting the list of videos from YouTube
$relatedVideos = $RYV->searchYouTube( $args['search'], $args['orderBy'], $args['start'], $args['max'], $args['apiVersion'] );

// Display the list as an unordered HTML list
echo $RYV->displayResults(
  $relatedVideos,
  $args
);
`

== Frequently Asked Questions ==

If you have any question, any kind of suggestion, or maybe a feature request, please let me know. All feedback except trolling is welcome!

== Screenshots ==

1. The widget backend for customizing the video request.

== Changelog ==

= 1.0.3 =
* Added a ReadMe or How-To page to the WordPress backend under Settings > Related YT Videos

= 1.0.2 =
* Plugin launch.

== Upgrade Notice ==

No notices for now.
