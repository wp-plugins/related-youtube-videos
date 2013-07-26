<?php
/**
 * Plugin class for the relatedYouTubeVideos plugin.
 *
 * @package     relatedYouTubeVideos
 * @copyright   Copyright (c) 2013 http://www.meomundo.com
 * @author      Christian Doerr <doerr@meomundo.com>
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPL-2.0)
 *
 */
class RelatedYouTubeVideos extends Meomundo_WP {
  
  /**
   * The constructor.
   *
   * @param string $absolutePluginPath Absolute path to the plugin directory.
   * @param string $pluginURL URL to the plugin directory.
   * @param string $pluginSlug Plugin handler.
   */
  public function __construct( $absolutePluginPath, $pluginURL, $pluginSlug ) {
    
    parent::__construct( $absolutePluginPath, $pluginURL, $pluginSlug );
    
    // Shortcode
    add_shortcode( 'relatedYouTubeVideos', array( $this, 'handleShortcode' ) );

    // Widget
    add_action( 'widgets_init', create_function( '', 'register_widget( "RelatedYouTubeVideos_Widget" );' ) );
    
    // Add a README Page (will appear under Settings > Related YouTube Videos)
    add_action( 'admin_menu', array( $this, 'registerBackend' ) );

  }

  /**
   * Handle Shortcode [relatedYouTubeVideos]
   *
   * Attributes for further configuration:
   *  'width'       (numeric)   Width of the HTML video object
   *  'height'      (numeric)   Height of the HTML video object
   *  'terms'       (string)    Search YouTube for these terms
   *  'orderBy'     (string)    Can either be 'published', 'rating', 'viewCount', (default) 'relevance'.
   *  'start'       (numeric)   Offset / numbers of search results that will be skipped - could in theory be used for pagination.
   *  'max'         (numeric)   Number of videos (or search results) that will be returned. Can be any number between 1 and 10!
   *  'apiVersion'  (numeric)   Version of the YouTube/Google API that will be used.
   *  'class'       (string)    You can specify an additional HTML class name for the wrapping <ul> element
   *  'id'          (string)    You can specify the HTML id attribute for the wrapping <ul> element.
   *  'relation'    (string)    Specify the kind of relation that shall be used for searching YouTube. Can either be 'postTile', 'postTags', or 'keywords' (in which case the attribute 'keywords' will be used).
   *
   * @param array $atts Array of shortcode attributes - provided by the WordPress shortcode API
   */
  public function handleShortcode( $atts ) {

    /**
     * Let the API do the heavy lifting.
     */
    $this->loadClass( 'RelatedYouTubeVideos_API' );
    
    $API          = new RelatedYouTubeVideos_API();

    $data         = $API->validateConfiguration( $atts );

    /**
     * Custom Defaults for the shortcode environment
     */
    if( $data['width'] == 0 ) {
      
      $data['width'] = 720;
      
    }

    if( $data['height'] == 0 ) {
      
      $data['height'] = 480;
      
    }
    
    $results      = $API->searchYouTube(
      array(
        'searchTerms' => $data['search'],
        'orderBy'     => $data['orderBy'],
        'start'       => $data['start'],
        'max'         => $data['max'],
        'apiVersion'  => $data['apiVersion'],
        'exact'       => $data['exact'],
        'random'      => $data['random']
      )
    );

    /**
     * View/Return the search results in form of an unordered HTML list.
     */
    return $API->displayResults(
      $results,
      array(
        'id'      => 'relatedVideos',
        'width'   => $data['width'],
        'height'  => $data['height'],
        'class'   => $data['class'],
        'id'      => $data['id']
      )
    );
    
  }
  
  /**
   * Register Backend Pages
   */
  public function registerBackend() {
    
    // ReadMe or How To
    $readme = add_options_page( 'Related YouTube Videos', 'Related YT Videos', 'edit_posts', $this->slug . '_readme', array( $this, 'showReadmePage' ) );
    
  }

  /**
   * Display the ReadMe / How To Page
   */
  public function showReadmePage() {
    
    if( file_exists( $this->path . 'readmePage.html' ) ) {
      
      echo file_get_contents( $this->path . 'readmePage.html' );

    }
    else {
      
      echo '<h2>File Not Found!</h2>';
      
    }
    
  }

}
?>