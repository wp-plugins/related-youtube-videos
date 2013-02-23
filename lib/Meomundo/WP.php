<?php
/**
 * Basic WordPress class to build on when developing themes and plugins.
 *
 * @category    MeoLib
 * @package     WordPress
 * @copyright   Copyright (c) 2012 http://www.meomundo.com
 * @author      Christian Doerr <doerr@meomundo.com>
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPL-2.0)
 *
 */
class Meomundo_WP extends Meomundo {

  /**
   * @var string $url URL to the theme or plugin directory.
   */
  protected $url;
  
  /**
   * @var string slug Unique theme or plugin slug. Will, for example, be used to store options or handle backend pages.
   */
  protected $slug;
  
  /**
   * The constructor.
   *
   * @param string $absolutePath Absolute path to the theme or plugin directory.
   * @param string $themeOrPluginURL URL to the theme or plugin directory.
   * @param string $sluf Unique theme or plugin slug. Will, for example, be used to store options or handle backend pages.
   */
  public function __construct( $absolutePath, $themeOrPluginURL, $slug ) {
    
    $this->url      = $themeOrPluginURL;

    $this->slug     = $slug;

    parent::__construct( $absolutePath );
    
  }

  /**
   * Helper: Escape or strip "evil" code from a given string.
   *
   * @param string $string String that shall be prepared.
   * @param boolean $slash (Optional) TRUE for adding slashes, FALSE for removing slashes or leaving this one out for neither of those operations.
   * @return string Prepared and hopefully more secured string.
   */
  public function prepareString( $string, $slash = null ) {

    $string = (string) $string;
    
    $string = strip_tags( $string );

    $string = htmlspecialchars( $string, ENT_QUOTES );

    if( $slash === true ) {
      
      $string = addslashes( $string );
    
    }
    elseif( $slash === false ) {
      
      $string = stripslashes( $string );
      
    }
    
    return $string;
    
  }

  
}
?>