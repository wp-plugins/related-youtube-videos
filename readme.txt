=== Related YouTube Videos ===

Contributors:       Zenation
Donate link:        https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5K6UDDJRNKXE2
Tags:               videos, youtube, related
Requires at least:  3.0.0
Tested up to:       3.8.1
Stable tag:         1.5.8
License:            GPLv2
License URI:        http://www.gnu.org/licenses/gpl-2.0.html

Automatically embed YouTube videos that are related to your content.

== Description ==

**Related YouTube Videos** is a free WordPress plugin that embeds a number of, well, YouTube videos that are related to your content. The list is put together by using the YouTube search API. And you can specify the relation between the videos and your content by:

* the title of your current post, page or custom post type.
* the tags of your current post, page or custom post type.
* any keywords you specify.

Also, this plugin offers you two ways to embed related videos:

1. by using the shortcode [relatedYouTubeVideos] somewhere in your post/page content.
2. by using the WordPress widget "Related YouTube Videos" in any of your widget areas (multiple instances are possible).



= The Shortcode =

I'm only going to explain the options/paramters for the shortcode but they work exactly the same for the widget, of course.

You can put the shortcode **[relatedYouTubeVideos]** anywhere you want inside the content of a page, post, or custom post type - as long as your WordPress theme is supporting shortcodes.

You can use the following options/parameters/attributes:

**Appearance**

* **id**                  - You can specify the HTML id attribute for the wrapping `<ul>` element.
* **class**               - You can specify an additional HTML class name for the wrapping `<ul>` element
* **width**               - Width of the HTML video object
* **height**              - Height of the HTML video object
* **preview**             - "true" will only display the preview image and only load the video (via Javascript!) when this image has been clicked.
* **showVideoTitle**      - "true" if you want to show the video title right below the video itself. Can be styled via CSS class `.title`
* **showVideoDescripton** - "true" if you want to show the video description below the video, respectively, when the video title is displayed right below the title. Can be styled via CSS class `.description` 

**Configuration**

* **max**                 - Number of videos (or search results) that will be returned. Can be any number between 1 and 10!
* **random**              - Select MAX number of videos out of RANDOM number of videos.
* **offset**              - START and OFFSET are interchangably the same and just other words for the same option.

**YouTube**

* **relation**            - Specify the kind of relation that shall be used for searching YouTube. Can either be 'postTitle', 'postTags', or 'keywords' (in which case the attribute 'terms' will be used for the YouTube search).
* **terms**               - Search YouTube for these terms - no separating commas required.
* **exact**               - Set to 'true' will (try to) search for the exact phrase.
* **orderBy**             - Can either be 'published', 'rating', 'viewCount', (default) 'relevance'.
* **lang**                - {2-letter-language-code} will show videos in that language.
* **region**              - {2-letter-country-code} will show videos that are actually viewable in that region/country.
* **author**              - Only show videos from a given YouTube User(name) .
* **filter**              - Add additional keywords or filtering search parameters. Those will **always** be added even when the relation is set to post title, tags, or so.

I recommend always using the attributes 'relation', 'max', and if the relation shall be 'keywords' the 'terms' attribute. Depending on your design you might also set a custom width and height for the videos so they fit in properly.

Shortcode Example 1: **[relatedYouTubeVideos relation="postTags" max="3"]** Will show three videos coming back from the search YouTube for (all!) the tags you have assigned to this post or page.

Shortcode Example 2: **[relatedYouTubeVideos relation="keywords" terms="monty python" max="5"]** Will show five Monty Python videos from YouTube.

Shortcode Example 3: **[relatedYouTubeVideos relation="keywords" terms="real madrid" exact="true" max="2"]** Will search for the exact phrase "Real Madrid" and (hopefully) not just anything "real".

Shortcode Example 4: **[relatedYouTubeVideos relation="postTitle" max="1" orderBy="viewCount" start="1"]** Will show the second most popular video (the first being skipped) relating to your post or page title.

Shortcode Example 5: **[relatedYouTubeVideos relation="keywords" terms="monthy python" max="1" showVideoTitle="true" showVideoDescription="true"]** Will show a Monty Python video, followed by the video title, followed by the video, followed by the video description.

Shortcode Example 6: **[relatedYouTubeVideos relation="keywords" terms="monthy python" max="1" preview="true"]** Will show the thumbnail of a Monty Python video and load + play the video only when it's being clicked.

Shortcode Example 7: **[relatedYouTubeVideos relation="postTitle" filter="intitle:official -intitle:cover" max="1" preview="true"]** If the post title is about a music video (band and song title, for example) this will only show the official music video.

= The Widget =

The widget almost works the same way. Or at least it has the same options for configuring the video request. If you log into your WordPress backend and go to the "Appearance > Widget" menu (given that your theme supports widgets) you can drag&drop a widget instance into the widget area of your choice.

The *relatedYouTubeVideos* widget allows multiple instances. So you can put as many widgets as you like into as many widget areas as you like.

The difference between the widget and the shortcode is not in terms of functionality but usually in the context they reside. Widgets usually go into sidebars or footers and alike and the same widget usually shows up for many, if not all pages, just the same. Shortcodes are placed inside the actual content and therefore will only show up when the page or post where they're put is shown.

= Randomize Results =

The same keywords (or post title/tags) will ususally return the same video(s) for a period of time. Basically, that's up to YouTube but in can take days, weeks or even months until fresh videos will show up.

When you set a numeric value for the RANDOM parameter/option you can get random videos from a pool of results. The MAX value plays along with the RANDOM value and both read like this: Show me {MAX} random videos out of {RANDOM} videos.

**[relatedYouTubeVideos relation="keywords" terms="fast cars" max="2" random="10"]** will actually request 10 videos from YouTube but only show 2 random ones out of that 10.

So RANDOM will determine the size of the pool MAX videos will be chosen from.

= System Requirements =

In order to run this plugin you need the following components installed and enabled in your server and PHP environment:

* PHP 5.1.2+
* SimpleXML (usually is enabled by default anyways)
* cURL (preferred) or fopen/fsockopen
* OpenSSL + HTTPS wrapper (is only required if cURL is not available)
* WordPress 3.0+

In general you should not have to worry about these things since they're included in most web hosting packages nowadays.
But to be sure you can download and install this plugin and then check the backend page. There is a "System Requirements" section that will show you exactly if you can good to go or if there is any problem.

In case you're getting a "URL file-access is disabled in the server configuration" error you should make sure your PHP.ini file contains these two lines:

  allow_url_include = on
  
  allow_url_fopen = on

= Available Languages: =
* English
* German
* Serbo-Croatian - by Borisa Djuraskovic ([Webhostinghub](http://www.webhostinghub.com/))


== Installation ==

Basically, there are three ways to install this or any other WordPress plugin.

= The WordPress Plugin Installer =

Log into the WordPress backend as someone who has the right to install and activate new plugins. Go the "Plugins" section and click the "Add New" button at the top of the page. Enter "relatedYouTubeVideos" into the search field and hit the "Search Plugins" button. Look out for this plugin and click the "install now" link under the plugin name in the first column. After the plugin has been installed automatically you only have to click the "Activate Plugin" link and you're good to go.

= Uploading The ZIP file Yourself =

You can also download the current plugin ZIP file, for example from [http://www.WordPress.org/extend/plugins/related-youtube-videos/](http://www.WordPress.org/extend/plugins/related-youtube-videos/) and upload it yourself.

Just log into the WordPress backend as someone who has the right to install and activate new plugins. Go to the "Plugins" section and click the "Upload" link. Select the ZIP file from your local machine and hit the "Install Now" button. After the plugin has been installed you only have to click the "Activate Plugin" link and you're good to go.

= Upload via FTP =

You can also download the current plugin ZIP file, for example from [http://www.WordPress.org/extend/plugins/related-youtube-videos/](http://www.WordPress.org/extend/plugins/related-youtube-videos/), and upload it yourself via FTP, for example.

First you have to download the plugin ZIP file, for example from [http://www.WordPress.org/extend/plugins/related-youtube-videos/](http://www.WordPress.org/extend/plugins/related-youtube-videos/), and extract the archive on your local machine. Then start any FTP client of your choice (e.g. FileZilla) and connect to your web server. Browse to your WordPress' root directory and then into /wp-content/plugins/. Now upload the /related-youtube-videos/ folder you just extracted from the ZIP archive with all its containing files.

Then log into the WordPress backend as someone who has the right to install and activate new plugins. Go to the "Plugins" section and look for the "relatedYouTubeVideos" in your plugin list. If the plugin doesn't appear in this list, you might have to switch to the "Inactive" tab and look again. If you have found it just hit the "Activate" link below the plugin name in the first column and you are good to go.

== Other Notes ==

Developers can also use the API class outside the plugin context, for example in a theme template file. All you have to do is include the class (if it doesn't already exist) and create an object like this:

`
/* Load the "Related YouTube Videos" API class if it does not exist yet. */
if( !class_exists( 'RelatedYouTubeVideos_API' ) ) {

  $file = str_replace( '/', DIRECTORY_SEPARATOR, ABSPATH ) . 'lib' . DIRECTORY_SEPARATOR . 'RelatedYouTubeVidoes' . DIRECTORY_SEPARATOR . 'API.php';

  if( file_exists( $file ) ) {
    
    include_once $file;
    
  }

}
/* Only continue if the API class could be loaded properly. */
if( class_exists( 'RelatedYouTubeVideos_API' ) ) {

  $RytvAPI  = new RelatedYouTubeVideos_API();
  
  /* Do your configuration */
  $data     = $RytvAPI->validateConfiguration(
    array(
     'relation' => 'postTitle',
     'max'      => '3',
     'width'    => 150,
     'height'   => 150,
     'lang'     => 'en',
     'region'   => 'de',
     'class'    => 'left center inline bg-black',
     'preview'  => true
    )
  );

  /* Search YouTube. */
  $results  = $RytvAPI->searchYouTube( $data );

  /* Generate the unordered HTML list of videos according to the YouTube results and your configuration.  */
  $html     = $RytvAPI->displayResults( $results, $data );
  
  echo $html; // Or do with it whatever you like ;)

}
`


== Frequently Asked Questions ==

If you have any question, any kind of suggestion, or maybe a feature request, please let me know. All feedback except trolling is welcome!

== Screenshots ==

1. The widget backend for customizing the video request.

== Changelog ==

= 1.5.8 =
* Workaround: In case curl is not install simply try loading calling the YouTube API, no matter if the (officially) required HTTPS wrapper is installed or not.

= 1.5.7 =
* Fix of decting the method that shall be used to call the remote YouTube API.

= 1.5.5 =
* Behaviour fixed when YT is not returning a single result/video.

= 1.5.4 =
* Randomizer maths fix.

= 1.5.3 =
* Quick typo fix in the JS code. Oh boy^^

= 1.5.2 =
* Fix: Preview mode now shows title and description again (it you want it to).


== Upgrade Notice ==

No notices for now.
