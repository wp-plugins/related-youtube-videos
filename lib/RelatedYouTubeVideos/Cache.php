<?php
class RelatedYouTubeVideos_Cache {
  
  protected $cacheDir;
  
  public function __construct( $cacheDir = '' ) {
    
    $this->cacheDir = ( $cacheDir === '' || !is_dir( $cacheDir ) ) ? realpath( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR : $cacheDir;
    
  }
  
  public function clearCache() {
    
    $files    = glob( $this->cacheDir . '*' );
    
    foreach( $files as $file ) {
    
      if( is_file( $file ) ) {
        
        unlink( $file );
    
      }
    
    }
    
  }
  
}
?>