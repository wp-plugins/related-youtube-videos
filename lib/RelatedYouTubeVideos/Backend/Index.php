<?php
class RelatedYouTubeVideos_Backend_Index extends Meomundo_WP {
  
  /**
   * @var array $relations List of supported relations between your content and the YouTube videos.
   */
  protected $relations;
  
  /**
   * The constructor.
   *
   * @param string $absolutePluginPath    Absolute path to the plugin directory.
   * @param string $pluginURL             Absolute URL to the plugin directory.
   * @param string $pluginSlug            Plugin handle.
   */
  public function __construct( $absolutePluginPath, $pluginURL, $pluginSlug ) {
    
    parent::__construct( $absolutePluginPath, $pluginURL, $pluginSlug );
    
    $this->relations = array(
      'posttitle'       => _x( 'Post Title', 'settings', $this->slug ),
      'posttags'        => _x( 'Post Tags', 'settings', $this->slug ),
      'postcategories'  => _x( 'Post Categories', 'settings', $this->slug ),
      'keywords'        => _x( 'Custom Keywords', 'settings', $this->slug )
    );
    
    // Sort by value, not key. And since the "labels" can be different in different languages the sorting needs to be done after the initiation.
    asort( $this->relations );
    
  }
  
  /**
   * The page controller.
   *
   * @return string HTML of the page.
   */
  public function controller() {

    $version  = $this->getPluginVersion();
    
    $html     = '';
    
    $html     .= '<p class="credits" style="float:right;padding-right:1em;">by <a href="http://www.meomundo.com/" title="meomundo.com">Chris Doerr</a></p>' . "\n";

    $html     .= '<h2 id="rytv_logo">Related YouTube Videos' . $version . "</h2>\n";
    
    // If the settings form has been sent do the validation
    if( isset( $_POST['rytv_settings_status'] ) && (int) $_POST['rytv_settings_status'] === 1 ) {
      
      $html   .= $this->validateSettings();
      
    }
    
    /**
     * The number of settings is so small that I thought it would be okay to integrate
     * the settings form as well as the documentation in one single page.
     */
    $html     .= $this->viewSettingsForm();
    
    $html     .= $this->viewSystemRequirements();
    
    $html     .= $this->viewDocumentation();
    
    return $html;
    
  }
  
  /**
   * Helper: Get the current plugin version.
   *
   * @return string Either an empty string or the alread HTML-formatted version number (in brackets).
   */
  protected function getPluginVersion() {

    $data     = @get_plugin_data( $this->path . 'index.php' );
    
    $version  = ( isset( $data['Version'] ) ) ? ' <small>(v' . $data['Version'] . ')</small>' : '';
    
    return $version;

  }
  
  /** 
   * View / Build the settings form.
   *
   * @return string HTML of the settings form.
   */
  public function viewSettingsForm() {
    
    $options          = get_option( $this->slug );
    
    $html             = '';


    $loadThemes       = ( !isset( $options['loadThemes'] ) || $options['loadThemes'] === true ) ? true : false;
    
    $defaultRelation  = ( isset( $options['defaultRelation'] ) ) ? strtolower( $options['defaultRelation'] ) : 'posttitle';
    

    $html .= '<form action="admin.php?page=' . $this->slug . '_index" method="post" id="rytv_settings">' . "\n";

    $html .= ' <h3>' . _x( 'Settings', $this->slug ) . "</h3>\n";

    $html .= '<input type="hidden" name="rytv_settings_status" value="1" />' . "\n";
    
    $html .= '<table class="settings">' . "\n";

    // Setting: Load Themes
    $html .= " <tr>\n";
    $html .= '  <th><input type="checkbox" name="rytv_settings_loadThemes" value="on" id="rytv_settings_loadThemes"';
    
    if( $loadThemes === true ) {
      
      $html .= ' checked="checked"';
      
    }
    
    $html .= ' /></th>' . "\n";
    $html .= '  <td> <label for="rytv_settings_loadThemes">' . _x( 'Load CSS theme file in the frontend.', $this->slug ) . '</label></td>' . "\n";
    $html .= " </tr>\n";
    
    // Setting: Default Relation
    $html .= " <tr>\n";
    $html .= '  <th><label for="rytv_settings_defaultRelation">' . _x( 'Default Relation', $this->slug ) . ':</label></th>' . "\n";
    $html .= '  <td>' . "\n";
    $html .= '   <select size="1" name="rytv_settings_defaultRelation" id="rytv_settings_defaultRelation">' . "\n";
    
    foreach( $this->relations as $value => $label ) {

      $html .= '    <option value="' . $value . '"';
      
      if( $defaultRelation === $value ) {
        
        $html .= ' selected="selected"';
        
      }
      
      $html .= '>' . $label . '</option>' . "\n";
      
    }
    
    $html .= "   </select>\n";
    $html .= "  </td>\n";
    
    $html .= " </tr>\n";
    

    $html .= "</table>\n";

    $html .= '<p><input type="submit" value="' . _x( 'Save Changes', $this->slug ) . '" class="button-primary" /></p>' . "\n";

    $html .= "</form>\n";

    
    return $html;
    
  }
  
  /**
   * Validate and save the settings form data.
   *
   * @return string HTML formatted status message about the options being saved or not.
   */
  public function validateSettings() {

    // The old settings as "default"
    $old                      = get_option( $this->slug );
    
    if( $old === false ) {
      
      $old                    = array();
      
    }
    

    // The new settings via the sent settings form.
    $new['loadThemes']        = ( isset( $_POST['rytv_settings_loadThemes'] ) ) ? true : false;
    

    $new['defaultRelation']   = ( isset( $_POST['rytv_settings_defaultRelation'] ) && array_key_exists( strtolower( $_POST['rytv_settings_defaultRelation'] ), $this->relations ) ) ? strtolower( $_POST['rytv_settings_defaultRelation'] ) : '';
    
    if( $new['defaultRelation'] === '' ) {
      
      $new['defaultRelation'] = ( isset( $old['defaultRelation'] ) ) ? $old['defaultRelation'] : 'posttitle';
      
    }
    
    /**
     * The WP options update function also returns FALSE in case there was no change in the options.
     * So in order to do a proper error handling the following apploach needs to be taken.
     */

    if( $new !== $old ) {
      
      $data             = array_merge( $old, $new );
      
      $status           = update_option( $this->slug, $data );
      
      if( $status === false) {
        
        return _x( '<p class="meoMessage error">Data could not be saved!</p>', $this->slug );
      
      }
      
    }
    
    return _x( '<p class="meoMessage success">Changes have been saved.</p>', $this->slug );

  }
  
  /**
   * View / read the plugin documentation, in the proper lanuguage if availabel or in English (=default)
   *
   * @return string HTML formatted plugin documentation.
   */
  public function viewDocumentation() {
    
    // Detect the two-letter language code of the currently used language.
    $lang       = strtolower( substr( (string) get_locale(), 0, 2 ) );
    

    $docPath    = $this->path . 'docs' . DIRECTORY_SEPARATOR;
    
    $docFile    = $docPath . $lang . 'html';
    
    // In case there is a language version for the currently used language load its content.
    if( file_exists( $docFile ) ) {
      
      $content  = file_get_contents( $docFile );
      
    }
    // Otherwise load the English documentation.
    else {
      
      $content  = ( file_exists( $docPath . 'en.html' ) ) ? file_get_contents( $docPath . 'en.html' ) : '<p><i>Error: Missing doc file!</i></p>';
      
      $lang     = 'en';
      
    }
    
    $html       = '<h3>' . _x( 'Documentation', $this->slug ) . ' (' . $lang . ')</h3>';
    
    $html       .= $content;

    return $html;
    
  }
  
  public function viewSystemRequirements() {
    

    $simpleXML  = function_exists( 'simplexml_load_file' );
    
    $curl       = ( in_array( 'curl', get_loaded_extensions() ) );
    
    $tmp        = ini_get( 'allow_url_fopen' );

    $fopen      = ( (int) $tmp === 1 || strtolower( $tmp ) === 'on' || (bool) $tmp === true ) ? true : false;
    
    $openSSL    = extension_loaded( 'openssl' );

    $wrappers   = stream_get_wrappers();

    $https      = in_array( 'https', $wrappers );
    
    $goodToGo   = false;
    
    if( $simpleXML === true && ( $curl === true || ( $fopen === true && $https === true ) ) ) {
      
      $goodToGo = true;
      
    }
    
    $html = '<h3>' . _x( 'System Requirements', $this->slug ) . "</h3>\n";
    
    $html .= '<ul class="sysreq">' . "\n";
    
    $html .= ' <li class="' . ( ( $simpleXML === true ) ? 'success' : 'error' ) . '">SimpleXML</li>' . "\n";

    $html .= ' <li class="' . ( ( $curl === true ) ? 'success' : 'error' ) . '">cURL</li>' . "\n";

    $html .= ' <li class="' . ( ( $fopen === true ) ? 'success' : 'error' ) . '">fOpen</li>' . "\n";

    $html .= ' <li class="' . ( ( $https === true ) ? 'success' : 'error' ) . '">HTTPS wrapper - <em>' . _x( 'In case no video is being displayed you might have to install this one on your server.', $this->slug ) . "</em></li>\n";

    $html .= "</ul>\n";
    
    if( $goodToGo === true ) {
      
      $html .= '<p class="sysreq success">' . _x( 'You should be fine.', $this->slug ) . "</p>\n";
    
    }
    else {
      
      $html .= '<p class="sysreq error">' . _x( 'It seems that at least one of the requirements are <strong>not</strong> met. Please check your server and PHP environment!', $this->slug ) . "</p>\n";
      
    }
    
    return $html;
    
  }

}
?>