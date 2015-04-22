<?php

$rootPath = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;

if( !class_exists( 'RelatedYouTubeVideos_Cache' ) ) {
  
  require_once( $rootPath . 'lib' . DIRECTORY_SEPARATOR . 'RelatedYouTubeVideos' . DIRECTORY_SEPARATOR . 'Cache.php' );

}

$Cache = new RelatedYouTubeVideos_Cache( $rootPath . 'cache' . DIRECTORY_SEPARATOR );

$Cache->clearCache();

?>