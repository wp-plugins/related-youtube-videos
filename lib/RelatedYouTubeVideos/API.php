<?php
/**
 * The plugin API.
 *
 * @package     relatedYouTubeVideos
 * @copyright   Copyright (c) 2013 http://www.meomundo.com
 * @author      Christian Doerr <doerr@meomundo.com>
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPL-2.0)
 *
 */
class RelatedYouTubeVideos_API {

  /**
   * Do the actual YouTube search by generating a GET request.
   *
   * @param array   $args       An array of parameters.
   * 
   * string   searchTerm    These terms will be user to search YouTube for results.
   * bool     exact         Will search for the exact phrase that has been set in the 'searchTerms' parameter.
   * string   orderBy       Order the search results by a given set of rules.
   * int      start         Number of videos/search results that will be skipped.
   * int      max           Number of videos/search results that will be returned.
   * int      apiVersion    Verion of the YouTube API that shall be used.
   *
   * @return mixed              Will return FALSE in case the request was invalid or some other error has occured (like a timeout) or an array containing the search results.
   */
/*
  // Downwards compatibility?!
  public function searchYouTube( $args, $orderBy = '', $start = '', $max = '', $apiVersion = 2 ) {
  
    if ( !is_array( $args ) ) {
      
      $args = array();
      
      $args['searchTerms']  = $args;
      $args['orderBy']      = $orderBy;
      $args['start']        = $start;
      $args['max']          = $max;
      $args['apiVersion']   = $apiVersion;
      
    }
*/  
  public function searchYouTube( $args ) {

    $searchTerms  = isset( $args['searchTerms'] ) ? $args['searchTerms']      : '';

    $orderBy      = isset( $args['orderBy'] )     ? $args['orderBy']          : '';

    $start        = isset( $args['start'] )       ? $args['start']            : '';

    $max          = isset( $args['max'] )         ? $args['max']              : '';

    $apiVersion   = isset( $args['apiVersion'] )  ? (int) $args['apiVersion'] : 2;

    $exact        = ( isset( $args['exact'] ) && $args['exact'] === true ) ? true : false;

    $searchTerms  = ( $exact === true ) ? '%22' . urlencode( $searchTerms ) . '%22' : urlencode( $searchTerms );
    
    $orderBy      = urlencode( $orderBy );
    
    $start        = (int) $start +1;
    
    $max          = (int) $max;
  
    $target       = 'http://gdata.youtube.com/feeds/api/videos?q=' . $searchTerms . '&orderby=' . $orderBy . '&start-index=' . $start . '&max-results=' . $max . '&v=2';

    // @todo (future feature) $target caching with the filename containing the blog ID for MultiSite use!
    $xml          = simplexml_load_file( $target );

    if( !is_object( $xml ) ) {
    
      return false;
    
    }
    
    if( isset( $xml->errors->error->code ) ) {
      
      return array( 'error' => $xml->errors->error->code );
      
    }

    $result       = array();

    foreach( $xml->entry as $video ) {

      $result[]    = $video;

    }
    
    return $result;

  }

  /**
   * Take the array of search results and generate an unordered HTML list out of it.
   * The HTML for embedding the videos is hopefully valid (x)HTML!
   *
   * @param   array   $results  An array of YouTube search results.
   * @param   array   $args     An array for further (plugin) configuration.
   * @return  string            Unorderd HTML list.
   */
  public function displayResults( $results, $args = array() ) {

    /**
     * These kinds of errors should not break your site. So instead of echoing the error message in a way that's visibile
     * to everyone, it'll only up in the HTML source code in form of a comment line.
     */
    if( !is_array( $results ) || empty( $results) ) {
      
      return '<!-- [relatedYouTubeVideos] Error: No related videos found! -->';
      
    }
    
    if( isset( $results['error'] ) ) {

      return '<!-- [relatedYouTubeVideos] Error: ' . str_replace( '_', ' ', $results['error'] ) . '! -->';

    }
    
    $class  = isset( $args['class'] )   ? 'class="relatedYouTubeVideos ' . strip_tags( $args['class'] ) . '"' : 'class="relatedYouTubeVideos"';
    
    $id     = isset( $args['id'] )      ? 'id="' . strip_tags( $args['id'] ) . '"'                            : '';
    
    $width  = isset( $args['width'] )   ? (int) $args['width']  : 0;
    
    $height = isset( $args['height'] )  ? (int) $args['height'] : 0;

    /**
     * Starting the HTML generation.
     */
    $html   = '';
    
    $html   .= '  <ul class="youtubeResults">' . "\n";

    foreach( $results as $video ) {

      // Try detecting the YouTube Video ID 
      preg_match( '#\?v=([^&]*)&#i', $video->link['href'], $match );
  
      $videoID    = isset( $match[1] )      ? (string) $match[1]          : null;
  
      $videoTitle = isset( $video->title )  ? strip_tags( $video->title ) : 'YouTube Video';
  
      $html .= "   <li>\n";

      /**
       * This is meant to be valid (x)HTML embedding of the videos, so please correct me if I'm wrong!
       */
      if( $videoID != null ) {

        $html .= '    <object type="application/x-shockwave-flash" data="http://www.youtube.com/v/' . $videoID  . '" width="' . $width . '" height="' . $height . '">' . "\n";
        $html .= '     <param name="movie" value="http://www.youtube.com/v/' . $videoID . '" />' . "\n";
        $html .= '     <param name="wmode" value="transparent" />' . "\n";
        $html .= '     <a href="http://www.youtube.com/watch?v=' . $videoID . '"><img src="http://img.youtube.com/vi/' . $videoID . '/0.jpg" alt="' . $videoTitle . '" /><br />YouTube Video</a>' . "\n";
        $html .= "    </object>\n";
  
      }
      else {

        $html .= '  <li><a href="' . $video->link['href'] . '" title="' . $videoTitle . '">' . $videoTitle . '</a></li>';

      }

      $html .= "   </li>\n";

    }

    $html   .= "  </ul>\n";

    return $html;
    
  }

  /**
   * Validate a configurational array.
   *
   * @param array $args Array for configuring the YouTube search as well as the plugin output.
   */
  public function validateConfiguration( $args = array() ) {

    $title        = isset( $args['title'] )       ? strip_tags( trim( $args['title'] ) )    : '';

    $searchTerms  = isset( $args['terms'] )       ? strip_tags( trim( $args['terms'] ) )    : '';

    $orderBy      = isset( $args['orderBy'] )     ? strtolower( trim( $args['orderBy'] ) )  : '';
    
    /* Array indexes are case-sensitive^^ */
    $orderBy      = isset( $args['orderby'] )     ? strtolower( trim( $args['orderby'] ) )  : $orderBy;
    
    if( $orderBy !== 'published' && $orderBy !== 'rating' && $orderBy !== 'viewcount' ) {
      
      $orderBy    = 'relevance';
      
    }

    // looks like the YouTube API is case sensitive here!
    if( $orderBy == 'viewcount' ) {

      $orderBy = 'viewCount';

    }
    
    $start        = isset( $args['start'] )       ? (int) abs( $args['start'] )             : 0;
    
    if( $start < 0 ) {
      
      $start = 0;
      
    }
    
    $max          = isset( $args['max'] )         ? (int) abs( $args['max'] )               : 10;
    
    if( $max < 1 ) {
      
      $max = 1;
      
    }
    else if( $max > 10 ) {
      
      $max = 10;
      
    }
    
    $exact        = ( isset( $args['exact'] ) && ( $args['exact'] === true || $args['exact'] == 'true' || (int) $args['exact'] == 1 || $args['exact'] == 'on' ) ) ? true : false;

    $apiVersion   = isset( $args['apiVersion'] )  ? (int) abs( $args['apiVersion'] )        : 2;

    $width        = isset( $args['width'] )       ? (int) abs( $args['width'] )             : 0; // The default width should be specified in the calling environment (widget or shortode)

    $height       = isset( $args['height'] )      ? (int) abs( $args['height'] )            : 0; // The default height should be specified in the calling environment (widget or shortode)
    
    $class        = isset( $args['class'] )       ? strip_tags( $args['class'] )   	        : '';
    
    $id           = isset( $args['class'] )       ? strip_tags( $args['id'] )               : '';
    
    $relation     = isset( $args['relation'] )    ? strtolower( $args['relation'] )         : '';

    $wpSearch     = ( isset( $args['wpSearch'] ) && $args['wpSearch'] == true ) ? true      : false;  // Will only have an effect on the search results page

    if( $relation !== 'posttags' && $relation !== 'keywords' ) {
      
      $relation = 'posttitle';
      
    }

    if( $relation == 'posttitle' ) {
      
      global $post;
      
      $search = ( isset( $post->post_title ) ) ? $post->post_title : '';
      
    }
    else if( $relation == 'posttags' ) {
      
      global $post;
      
      if( isset( $post->ID ) ) {
      
        $tags   = wp_get_post_tags( $post->ID );
      
        $search = '';
      
        foreach( $tags as $tag ) {
        
          $search .= ' ' . $tag->name;
        
        }
      
        $search = trim( $search );
      
      }
      else {
        
        $search = '';
        
      }

    }
    else if( $relation == 'keywords' ) {
      
      $search = trim( $searchTerms );
      
    }

    return array(
      'title'       => $title,
      'terms'       => $searchTerms,
      'orderBy'     => $orderBy,
      'start'       => $start,
      'max'         => $max,
      'apiVersion'  => $apiVersion,
      'width'       => $width,
      'height'      => $height,
      'class'       => $class,
      'id'          => $id,
      'relation'    => $relation,
      'search'      => $search,
      'wpSearch'    => $wpSearch,
      'exact'       => $exact
    );

  }
  
}
?>