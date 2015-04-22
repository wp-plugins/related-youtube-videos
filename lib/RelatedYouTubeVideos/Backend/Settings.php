<?php
class RelatedYouTubeVideos_Backend_Settings extends Meomundo_WP {
  
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
    
    $customMessage    = ( isset( $options['customMessage'] ) ) ? $options['customMessage'] : '';
    
    $devKey           = ( isset( $options['devKey'] ) )       ? $options['devKey'] : '';
    
    $cachetime        = ( isset( $options['cachetime'] ) )    ? $options['cachetime'] : 24;
    
    if( $cachetime < 1 ) {
      
      $cachetime = 1;
      
    }
    

    $html .= '<form action="admin.php?page=' . $this->slug . '_settings" method="post" id="rytv_settings">' . "\n";

    $html .= ' <h3>' . _x( 'Settings', $this->slug ) . "</h3>\n";

    $html .= '<input type="hidden" name="rytv_settings_status" value="1" />' . "\n";
    
    $html .= '<table class="settings">' . "\n";
    
    // In order to use the YouTube API v3 you have to get your own DEVELOPER KEY!!
    $html .= " <tr>\n";
    $html .= '  <th><label for="rytv_settings_devKey">' . _x( 'YouTube Data API Key', $this->slug ) . ":</label></th>\n";
    $html .= '  <td><input type="text" name="rytv_settings_devKey" id="rytv_settings_devKey" value="' . $devKey . '" /></td>' . "\n";
    $html .= " </tr>\n";

    // Caching is required form now on!
    $html .= " <tr>\n";
    $html .= '  <th><label for="rytv_settings_cachetime">' . _x( 'Cache Time (in hours)', $this->slug ) . ":</label></th>\n";
    $html .= '  <td><input type="number" min="1" name="rytv_settings_cachetime" id="rytv_settings_cachetime" value="' . $cachetime . '" /></td>' . "\n";
    $html .= " </tr>\n";
    $html .= " <tr>\n";
    $html .= '  <th>&nbsp;</th>' . "\n";
    $html .= '  <td><a href="admin.php?page=' . $this->slug . '_clearcache" class="button-secondary">Clear Cache Manually</a></td>' . "\n";
    $html .= " </tr>\n";
    
    
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
    
    // Setting: Custom Message "No video(s) found"
    $html .= " <tr>\n";
    $html .= '  <th><label for="rytv_settings_customMessage">' . _x( 'Custom error message &quot;No Video(s) Found&quot;', $this->slug ) . ":</label></th>\n";
    $html .= '  <td><input type="text" name="rytv_settings_customMessage" id="rytv_settings_customMessage" value="' . $customMessage . '" /></td>' . "\n";
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
    
    $new['customMessage'] = ( isset( $_POST['rytv_settings_customMessage'] ) ) ? trim( $_POST['rytv_settings_customMessage'] ) : '';
    
    $new['devKey']        = ( isset( $_POST['rytv_settings_devKey'] ) ) ? trim( $_POST['rytv_settings_devKey'] ) : '';
    
    $new['cachetime']     = ( isset( $_POST['rytv_settings_cachetime'] ) ) ? (int) $_POST['rytv_settings_cachetime'] : 1;
    
    if( $new['cachetime'] < 1 ) {
      
      $new['cachetime'] = 1;
      
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

}
?>