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
    
    // Shortcode alias 1
    add_shortcode( 'RelatedYouTubeVideos', array( $this, 'handleShortcode' ) );

    // Shortcode alias 2
    add_shortcode( 'relatedyoutubevideos', array( $this, 'handleShortcode' ) );

    // Widget
    add_action( 'widgets_init', create_function( '', 'register_widget( "RelatedYouTubeVideos_Widget" );' ) );
    
    // Add backend pages
    add_action( 'admin_menu', array( $this, 'registerBackend' ) );
    
    // Add the themes css file only in the frontend and only if the option is not set to FALSE
    if( !is_admin() ) {
      
      $options = get_option( $this->slug );
      
      if( !isset( $options['loadThemes'] ) || $options['loadThemes'] === true ) {

        add_action( 'wp_enqueue_scripts', array( $this, 'registerFrontendStyles' ) );
      
      }

    }
    else {

      // Register styles and scripts to be used on the plugin's backend pages.
      add_action( 'admin_init', array( $this, 'adminInit' ) );

    }

  }
  
  /**
   * Admin ini action
   */
  public function adminInit() {
    
    // $wp_version
    
    // Register backend CSS for this plugin.
    wp_register_style( $this->slug . '_backendStyles', $this->url . 'css/backend.css' );
    
  }
  
  /**
   * Actually enqueue styles and scripts on certain backend pages.
   */
  public function loadBackendStyles() {

    wp_enqueue_style( $this->slug . '_backendStyles' );

  }
  
  /**
   * Load the theme.css file in the frontend.
   */
  public function registerFrontendStyles() {
    
    wp_enqueue_style( $this->slug . '_frontendStyles', $this->url . 'css/themes.css' );

  }
  
  /**
   * Handle the plugin shortcode.
   *
   * Sadly the regular expression that's being used for handling shortcodes does not allow
   * the /i option, meaning "not case-sensitive". Therefore the two most common spelling variations
   * will have also been registered as shortcodes that all will be handled by this very method.
   *
   * [relatedYouTubeVideos]
   * [RelatedYouTubeVideos]
   * [relatedyoutubevideos]
   *
   * @param array $atts Array of shortcode attributes, provided by the WordPress shortcode API
   */
  public function handleShortcode( $atts ) {

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
    
    /**
     * This is kind of a "manually normalized" way to call YouTube.
     *
     * Having to handling a thousand places like this when adding a new feature is prone to failure.
     * Therefore this will be replaced by some kind of automation in a future version!
     * The pattern is alread in testing :)
     */
    $results      = $API->searchYouTube(
      array(
        'searchTerms' => $data['search'],
        'orderBy'     => $data['orderBy'],
        'start'       => $data['start'],
        'max'         => $data['max'],
        'apiVersion'  => $data['apiVersion'],
        'exact'       => $data['exact'],
        'random'      => $data['random'],
        'duration'    => $data['duration'],
        'lang'        => $data['lang'],
        'region'      => $data['region'],
        'author'      => $data['author']
      )
    );

    /**
     * View/Return the search results in form of an unordered HTML list.
     */
    return $API->displayResults(
      $results,
      array(
        'id'                    => 'relatedVideos',
        'width'                 => $data['width'],
        'height'                => $data['height'],
        'class'                 => $data['class'],
        'id'                    => $data['id'],
        'showvideotitle'        => $data['showvideotitle'],
        'showvideodescription'  => $data['showvideodescription'],
        'preview'               => $data['preview'],
        'viewrelated'           => $data['viewrelated'],
        'autoplay'              => $data['autoplay']
      )
    );
    
  }
  
  /**
   * Register Backend Pages
   */
  public function registerBackend() {
    
    $page = add_menu_page( 'Related YouTube Videos', 'Related YouTube Videos', 'manage_options', $this->slug . '_index', array( $this, 'ViewBackend' ) );

    add_action( 'admin_print_styles-' . $page, array( $this, 'loadBackendStyles' ) );

  }

  /**
   * Show the plugin backend page.
   *
   * If there were multiple pages this method would act as controller.
   * But for now I've decided to implement only one single page
   * that contains of a fairly short settings area and the plugin documentation.
   *
   * Also, only load the code for the backend pages only when the page actually should be displayed.
   * This shoudl save some memory and maybe even CPU when you're in the backend but not on this plugin's page.
   */
  public function viewBackend() {
    
    $this->loadClass( 'RelatedYouTubeVideos_Backend_Index' );
    
    $Backend = new RelatedYouTubeVideos_Backend_Index( $this->path, $this->url, $this->slug );
    
    echo '<div class="wrap" id="rytv" name="top">';

    echo $Backend->controller();

    echo '</div>';
    
  }

}
?>