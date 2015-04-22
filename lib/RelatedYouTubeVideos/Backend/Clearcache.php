<?php
class RelatedYouTubeVideos_Backend_Clearcache extends Meomundo_WP {
  
  protected $Cache;
  
  /**
   * The constructor.
   *
   * @param string $absolutePluginPath    Absolute path to the plugin directory.
   * @param string $pluginURL             Absolute URL to the plugin directory.
   * @param string $pluginSlug            Plugin handle.
   */
  public function __construct( $absolutePluginPath, $pluginURL, $pluginSlug ) {
    
    parent::__construct( $absolutePluginPath, $pluginURL, $pluginSlug );
    
    $this->loadClass( 'RelatedYouTubeVideos_Cache' );
    
    $this->Cache = new RelatedYouTubeVideos_Cache( $this->path . 'cache' . DIRECTORY_SEPARATOR );

  }
  
  /**
   * The page controller.
   *
   * @return string HTML of the page.
   */
  public function controller() {
    
    $status = $this->Cache->clearCache();

    $returnURL  = 'admin.php?page=' . $this->slug . '_settings';

    $html = '';
    
    $html .= '<p class="message success">Cache has been cleared!</p>';
    
    $html .= '<p><a href="' . $returnURL . '">Return to the settings page</a>.</p>';
    
    // auto-redirect via JS after 3 seconds
    $html .= '<script>setTimeout(function(){window.location="' . $returnURL . '";}, 3000);</script>';
    
    return $html;
    
  }

}
?>