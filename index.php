<?php
/*
Plugin Name:  Related YouTube Videos
Plugin URI:   http://www.meomundo.com/
Description:  Embeds videos from YouTube that (can) automatically relate to a current page or post.
Author:       Chris Doerr
Version:      1.4.4
Author URI:   http://www.meomundo.com/
*/

/**
 * Basic configuration.
 */
$meoTemp = array(
  'path'  => dirname( __FILE__ ) . DIRECTORY_SEPARATOR,
  'url'   => plugins_url() . '/relatedYouTubeVideos/',
  'slug'  => 'relatedyoutubevideos'
);

/**
 * Making sure the core classes are available.
 */
if( !class_exists( 'Meomundo' ) ) {
  require_once $meoTemp['path'] . 'lib' . DIRECTORY_SEPARATOR . 'Meomundo.php';
}
if( !class_exists( 'Meomundo_WP' ) ) {
  require_once $meoTemp['path'] . 'lib' . DIRECTORY_SEPARATOR . 'Meomundo' . DIRECTORY_SEPARATOR . 'WP.php';
}
if( !class_exists( 'RelatedYouTubeVideos' ) ) {
  require_once $meoTemp['path'] . 'lib' . DIRECTORY_SEPARATOR . 'RelatedYouTubeVideos.php';
}
if( !class_exists( 'RelatedYouTubeVideos_Widget' ) ) {
  require_once $meoTemp['path'] . 'lib' . DIRECTORY_SEPARATOR . 'RelatedYouTubeVideos' . DIRECTORY_SEPARATOR . 'Widget.php';
}


/**
 * Language Files / Plugin Translations
 */
load_plugin_textdomain( $meoTemp['slug'], false,  basename( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR );

/**
 * The Plugin Object
 */
$RelatedYouTubeVideos = new RelatedYouTubeVideos( $meoTemp['path'], $meoTemp['url'], $meoTemp['slug'] );


/**
 * (Potentially) Free resources
 */
unset( $meoTemp );
?>