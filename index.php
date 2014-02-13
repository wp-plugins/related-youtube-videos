<?php
/*
Plugin Name:  Related YouTube Videos
Plugin URI:   http://www.meomundo.com/
Description:  Embeds videos from YouTube that (can) automatically relate to a current page or post.
Author:       Chris Doerr
Version:      1.5.9
Author URI:   http://www.meomundo.com/
*/

/**
 * Basic configuration.
 */
$meoTemp = array(
  'path'  => dirname( __FILE__ ) . DIRECTORY_SEPARATOR,
  'url'   =>  plugin_dir_url( __FILE__ ),
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
 * Which method should be used to load the external file / make the call to the YouTube webservice?
 */
if( !defined( 'RYTV_METHOD' ) ) {

  if( in_array( 'curl', get_loaded_extensions() ) ) {
    
    define( 'RYTV_METHOD', 'curl' );
    
  }
  else {
  
    $meoTemp['allow_url_fopen']           = ini_get( 'allow_url_fopen' );

    $meoTemp['fopen_isInstalled']         = ( (int) $meoTemp['allow_url_fopen'] === 1 || strtolower( $meoTemp['allow_url_fopen'] ) === 'on' || (bool) $meoTemp['allow_url_fopen'] === true ) ? true : false;
    
    $meoTemp['openSSL_isInstalled']       = extension_loaded( 'openssl' );

    $meoTemp['httpsWrapper_isInstalled']  = ( function_exists( 'stream_get_wrappers' ) && in_array( 'https', stream_get_wrappers() ) ) ? true : false;
    
    if(
         $meoTemp['fopen_isInstalled']        === true
      && $meoTemp['openSSL_isInstalled']      === true
      && $meoTemp['httpsWrapper_isInstalled'] === true
    ) {
      
      define( 'RYTV_METHOD', 'fopen' );
      
    }
    else {
      
      define( 'RYTV_METHOD', false );
      
    }
  
  }
  
}

/**
 * The Plugin Object
 */
$RelatedYouTubeVideos = new RelatedYouTubeVideos( $meoTemp['path'], $meoTemp['url'], $meoTemp['slug'] );


/**
 * (Potentially) Free resources
 */
unset( $meoTemp );
?>