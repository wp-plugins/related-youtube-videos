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
   * @todo remove!
   */
//  protected $apiKey = 'AIzaSyBJ3dEEiMCCfYmwVioaWhR91fgqrxX9xtM';
  protected $apiKey = '';
  
  /**
   * @var string $latestCall Store the latest URL call to the YouTube webservice.
   */
  protected $latestCall = '';
  
  /**
   * @var array $meta Acts as cache for storing post meta data like the tilte, categories, and tags.
   */
  protected $meta;
  
  /** 
   * @var int $timeout = PHP/Server settings: Max Execution Time - 5 seconds
   */
  protected $timeout;
  
  protected $rootPath;
  
  protected $cachePath;
  
  protected $options;

  /**
   * The Constructor
   */
  public function __construct() {
    
    $max_execution_time = (int) ini_get('max_execution_time');
    
    $this->timeout      = ( $max_execution_time > 0 ) ? ( $max_execution_time - 5 ) : 15;
    
    $this->rootPath     = realpath( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' ) . DIRECTORY_SEPARATOR;
    
    $this->cachePath    = $this->rootPath . 'cache' . DIRECTORY_SEPARATOR;
    
    if( !is_dir( $this->cachePath ) ) {
      @mkdir( $this->cachePath, 0775);
    }
    
    $this->options      = get_option( 'relatedyoutubevideos' );
    
    $this->apiKey       = ( isset( $this->options['devKey'] ) ) ? $this->options['devKey'] : '';
    
  }

  public function cacheIsValid( $cacheFile ) {
    
    if( !file_exists( $cacheFile ) ) {
      
      return false;
      
    }
    
    $now        = time();
    
    $filedate   = filemtime( $cacheFile );
    
    $cachetime  = ( isset( $this->options['cachetime'] ) ) ? (int) $this->options['cachetime'] : 24; // 24 hours is the default
    
    if( $cachetime < 1 ) {
      
      $cachetime = 1;
      
    }
    
    return ( $now < ( $filedate + $cachetime * 60 * 60 ) );
    
  }


  /**
   * Do the actual YouTube search by generating a GET request.
   *
   * @param   array   $args   An array of parameters.
   * @return  mixed           FALSE in case the request was invalid or some other error has occured (like a timeout) or an array containing the search results.
   */
  public function searchYouTube( $args ) {
    
    if( trim( $this->apiKey ) === '' ) {
      
      // @todo error message: Developer Key required!!
      return array();
      
    }

    $searchTerms  = isset( $args['searchTerms'] ) ? $args['searchTerms']      : '';
    
    if( $searchTerms == '' && isset( $args['terms'] ) ) {
      
      $searchTerms = $args['terms'];
      
    }

    if( $searchTerms == '' && isset( $args['search'] ) ) {
      
      $searchTerms = $args['search'];
      
    }
    
    $orderBy      = isset( $args['orderBy'] )     ? $args['orderBy']          : '';

    $start        = isset( $args['start'] )       ? $args['start']            : '';

    $max          = isset( $args['max'] )         ? $args['max']              : '';

    $apiVersion   = isset( $args['apiVersion'] )  ? (int) $args['apiVersion'] : 2;

    $lang         = ( isset( $args['lang'] ) && preg_match( '#^[a-z]{2}$#i', $args['lang'] ) ) ? strtolower( $args['lang'] ) : '';

    $region       = ( isset( $args['region'] ) && preg_match( '#^[a-z]{2}$#i', $args['region'] ) ) ? strtoupper( $args['region'] ) : '';

    $author       = ( isset( $args['author'] ) ) ? trim( $args['author'] ) : '';

    $exact        = ( isset( $args['exact'] ) && $args['exact'] === true ) ? true : false;

    $searchTerms  = ( $exact === true ) ? '%22' . urlencode( $searchTerms ) . '%22' : urlencode( $searchTerms );

    $orderBy      = urlencode( $orderBy );

    $duration     = ( isset( $args['duration'] ) && preg_match( '#^(short|medium|long)$#i', trim( $args['duration']  ) ) ) ? trim( strtolower( $args['duration'] ) ) : '';

    $start        = (int) $start +1;
    
    $max          = (int) $max;
    
    $random       = ( isset( $args['random'] ) && $args['random'] > $max )  ? (int) $args['random'] : $max;

    if( $random > $max ) {
      
      $target     = 'http://gdata.youtube.com/feeds/api/videos?q=' . $searchTerms . '&orderby=' . $orderBy . '&start-index=' . $start . '&max-results=' . $random . '&v=2';
      
    }
    else {
  
      $target     = 'http://gdata.youtube.com/feeds/api/videos?q=' . $searchTerms . '&orderby=' . $orderBy . '&start-index=' . $start . '&max-results=' . $max . '&v=2';
    
    }

    /**
     * Now that the basic URL has been build,
     * add optional parameter only when they're actually set.
     * Otherwise YouTube tends to invalidate the whole request sometimes!
     */

    // Optional: Duration
    if( $duration !== '' ) {
      
      $target     .= '&duration=' . $duration;
      
    }

    // Optional: Language
    if( $lang !== '' ) {
  
      $target     .= '&lr=' . $lang;

    }

    // Optional: Region/Country
    if( $region !== '' ) {

      $target     .= '&region=' . $region;

    }

    // Optional: Author/YouTube user
    if( $author !== '' ) {
  
      $target     .= '&author=' . $author;
  
    }


    /* The YouTube API v3 requires a whole new apprach but the old $target URL will be used to cache requests! */
    $urlParts = parse_url( $target );
  	
  	parse_str( $urlParts['query'], $query );
    
    ksort( $urlParts );
    
    // @todo too much overhead?!
    $cacheFile    = $this->cachePath . md5( serialize( $urlParts ) ) . '.json';
    
    if( file_exists( $cacheFile ) && $this->cacheIsValid( $cacheFile ) ) {
      
      $json       = file_get_contents( $cacheFile );
      
      $result     = json_decode( $json, true );
      
    }
    else {
    
      if( !class_exists( 'RelatedYouTubeVideos_Youtube' ) ) {
        
        require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'Youtube.php' );
      
      }

      $YouTube        = new RelatedYouTubeVideos_Youtube();

      $searchResults  = $YouTube->search( $query );
    
      $result         = $YouTube->extractVideos( $searchResults );
      
      if( is_dir( $this->cachePath ) ) {
      
        @file_put_contents( $cacheFile, json_encode( $result ) );
      
      }
    
    }
    

    /* {max} random videos out of {random} */
    if( $random > $max ) {

      $total      = count( $result );
      
      if( $total < $random ) {
        
        $random   = $total;
        
      }

      $count      = 0;
      
      $randIndex  = array();

      /* Generate random index numbers, between 0 and $random */
      while( $count < $max ) {
        
        $tmp = ( $random > 1 ) ? mt_rand( 0, ( $random -1 ) ) : 0;

        if( !in_array( $tmp, $randIndex ) ) {
          
          $randIndex[] = $tmp;
          
          $count++;
          
        }
        
      }

      /* Use the random index number so re-build the results array */
      $randResults  = array();

      foreach( $randIndex as $index ) {
        
        $randResults[] = $result[ $index ];
        
      }

      return $randResults;
      
    }
    else {
    
      return $result;
    
    }

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
      
      $options = get_option( 'relatedyoutubevideos' );
      
      $errMsg = '';
      
      if( isset( $options['customMessage'] ) && trim( $options['customMessage'] ) !== '' ) {
        
        $errMsg .= $options['customMessage'];
        
      }
      
      $errMsg .= "\n" . '<!-- relatedYouTubeVideos Error: No related videos found! (' . $this->latestCall . ') -->' . "\n";
      
      return $errMsg;
      
    }
    
    if( isset( $results['error'] ) ) {

      return '<!-- relatedYouTubeVideos Error: ' . str_replace( '_', ' ', $results['error'] ) . '! -->';

    }

    $class        = isset( $args['class'] )   ? 'class="relatedYouTubeVideos ' . strip_tags( $args['class'] ) . '"' : 'class="relatedYouTubeVideos"';
    
    $id           = ( isset( $args['id'] ) && !empty( $args['id'] ) ) ? 'id="' . strip_tags( $args['id'] ) . '"'                            : '';
    
    $width        = isset( $args['width'] )   ? (int) $args['width']  : 0;
    
    $height       = isset( $args['height'] )  ? (int) $args['height'] : 0;
    
    $viewRelated  = ( isset( $args['viewrelated'] ) && (int) $args['viewrelated'] === 0 ) ? 0 : 1;
    
    $autoplay     = ( isset( $args['autoplay'] ) && (int) $args['autoplay'] === 1 ) ? 1 : 0;

    /**
     * Starting the HTML generation.
     */
    $html   = '';
    
    /**
     * In PREVIEW mode only the images will be displayed. When clicked such an image will be replace with the video.
     * This requires Javascript to be enabled in the browser!!
     */
    if( isset( $args['preview'] ) && $args['preview'] === true ) {
      
      $jsFunction =<<<EOF
<script type="text/javascript">
if( typeof showRelatedVideo !== 'function' ) {
  function showRelatedVideo( config ) {
    
    'use strict';
    
    if( undefined === config.videoID ) {
      return '<i>Invalid Video ID</i>';
    }

    if( undefined === config.width ) {
      config.width = 480;
    }
    
    if( undefined === config.height ) {
      config.height = 360;
    }

    var video = '',
        videoTitle = ( undefined === config.title ) ? '' : config.title;
    
    video += '<object data="http://www.youtube.com/embed/' +  config.videoID + '?autoplay=1&rel={$viewRelated}" width="' + config.width +  '" height="' + config.height + '">';
    video += ' <param name="movie" value="http://www.youtube.com/v/' + config.videoID + '?rel={$viewRelated}" />';
    video += ' <param name="wmode" value="transparent" />';
    video += ' <param name="allowfullscreen" value="true" />';
    video += ' <a href="http://www.youtube.com/watch?v=' + config.videoID + '"><img src="http://img.youtube.com/vi/' + config.videoID + '/0.jpg" alt="' + videoTitle + '" /><br />YouTube Video</a>';
    video += '</object>';
    
    if( undefined !== config.title ) {
      video += '<div class="title">' + config.title + '</div>';
    }
    
    if( undefined !== config.description ) {
      video += '<div class="description">' + config.description + '</div>';
    }

    return video;

  }
}
</script>
EOF;

      $html                    .= $jsFunction;
      
      $html                    .= '  <ul ' . $class . ' ' . $id . '>' . "\n";

      foreach( $results as $video ) {

        $videoID                = isset( $video['videoID'] ) ? $video['videoID'] : null;
  
        $videoTitle             = isset( $video['title'] ) ? strip_tags( $video['title'] ) : 'YouTube Video';
        
        $videoTitle_clean       = $videoTitle;

        $videoTitle_esc         = preg_replace( array( '#"#im', "#'#im" ), array( '&quot;', '&rsquo;' ), $videoTitle );

        $videoDescription       = ( isset( $video['description'] ) ) ? $video['description'] : '';

        $videoDescription_clean = $videoDescription;
        
        $videoDescription_esc   = preg_replace( array( '#"#im', "#'#im" ), array( '&quot;', '&rsquo;' ), $videoDescription );

        $videoTitle             = ( isset( $args['showvideotitle'] ) && $args['showvideotitle'] === true ) ? ", title : '" . $videoTitle_esc . "'" : '';

        $videoDescription       = ( isset( $args['showvideodescription'] ) && $args['showvideodescription'] === true ) ? ", description : '" . $videoDescription_esc . "'" : '';
        
        $argsObj                = "{ videoID:'" . $videoID . "', width:" . $width . ", height:" . $height . $videoTitle . $videoDescription . "}";

        $html                  .= "   <li onClick=\"innerHTML=showRelatedVideo(" . $argsObj . ");removeAttribute('onClick');\">\n";

        if( $videoID != null ) {

          $html           .= '     <img src="http://img.youtube.com/vi/' . $videoID . '/0.jpg" alt="' . $videoTitle . '" width="' . $width . '" height="' . $height . '" />' . "\n";


          if( isset( $args['showvideotitle'] ) && $args['showvideotitle'] === true ) {
            
            $html         .= '    <div class="title">' . $videoTitle_clean . "</div>\n";
          
          }

          if( isset( $args['showvideodescription'] ) && $args['showvideodescription'] === true ) {
            
            $html         .= '    <div class="description">' . $videoDescription_clean . "</div>\n";
          
          }

        }
        else {

          $html           .= '  <li><a href="' . $video->link['href'] . '" title="' . $videoTitle . '">' . $videoTitle . '</a></li>';

        }

        $html             .= "   </li>\n";

      }

      $html               .= "  </ul>\n";

      
      $video              = null;
      
    }
    else {
    
      $html               .= '  <ul ' . $class . ' ' . $id . '>' . "\n";

      foreach( $results as $video ) {

// @todo fallback
/*
        // Try detecting the YouTube Video ID 
        preg_match( '#\?v=([^&]*)&#i', $video->link['href'], $match );
  
        $videoID          = isset( $match[1] )      ? (string) $match[1]          : null;
  
        $videoTitle       = isset( $video->title )  ? strip_tags( $video->title ) : 'YouTube Video';

        // @changelog 2014-10-03
        $videoDescription = ( $video->children('media', true)->group->children('media', true )->description ) ? (string) $video->children('media', true)->group->children('media', true )->description : '';
*/
      
      $videoID = isset( $video['videoID'] ) ? $video['videoID'] : null;
      $videoTitle = isset( $video['titel'] ) ? strip_tags( $video['title'] ) : 'YouTube Video';
      $videoDescription = isset( $video['description'] ) ? strip_tags( $video['description'] ) : '';
      
        $html             .= "   <li>\n";

        /**
         * This is meant to be valid (x)HTML embedding of the videos, so please correct me if I'm wrong!
         */
        if( $videoID != null ) {

          $html           .= '    <object data="http://www.youtube.com/embed/' . $videoID  . '?rel=' . $viewRelated . ( $autoplay === 1 ? '&autoplay=1' : '' ). '" width="' . $width . '" height="' . $height . '">' . "\n";
          $html           .= '     <param name="movie" value="http://www.youtube.com/v/' . $videoID . '?rel=' . $viewRelated . '" />' . "\n";
          $html           .= '     <param name="wmode" value="transparent" />' . "\n";
          $html           .= '     <param name="allowfullscreen" value="true" />' . "\n";
          $html           .= '     <a href="http://www.youtube.com/watch?v=' . $videoID . '"><img src="http://img.youtube.com/vi/' . $videoID . '/0.jpg" alt="' . $videoTitle . '" /><br />YouTube Video</a>' . "\n";
          $html           .= "    </object>\n";

          if( isset( $args['showvideotitle'] ) && $args['showvideotitle'] === true ) {
            
            $html         .= '    <div class="title">' . $videoTitle . "</div>\n";
          
          }
        
          if( isset( $args['showvideodescription'] ) && $args['showvideodescription'] === true ) {
            
            $html         .= '    <div class="description">' . $videoDescription . "</div>\n";
          
          }
  
        }
        else {

          $html           .= '   <li><a href="' . $video->link['href'] . '" title="' . $videoTitle . '">' . $videoTitle . '</a></li>';

        }

        $html             .= "   </li>\n";

        $video            = null;

      }

      $html               .= "  </ul>\n";
    
    }

    return $html;
    
  }

  /**
   * Validate a configurational array.
   *
   * @param   array $args   Array for configuring the YouTube search as well as the plugin output.
   * @return  array         Normalized data.
   */
  public function validateConfiguration( $args = array() ) {
    
    global $post;

    $title        = isset( $args['title'] )       ? strip_tags( trim( $args['title'] ) )    : '';

    $searchTerms  = isset( $args['terms'] )       ? strip_tags( trim( $args['terms'] ) )    : '';

    $orderBy      = isset( $args['orderBy'] )     ? strtolower( trim( $args['orderBy'] ) )  : '';
    
    /* Array indexes are case-sensitive^^ */
    $orderBy      = isset( $args['orderby'] )     ? strtolower( trim( $args['orderby'] ) )  : $orderBy;
    
    if( $orderBy !== 'published' && $orderBy !== 'rating' && $orderBy !== 'viewcount' ) {
      
      $orderBy    = 'relevance';
      
    }

    // The YouTube API is case sensitive!
    if( $orderBy === 'viewcount' ) {

      $orderBy    = 'viewCount';

    }
    
    // Start
    $start        = isset( $args['start'] )       ? (int) abs( $args['start'] )             : 0;
    
    if( $start === 0 && isset( $args['offset'] ) ) {
      
      $start      = (int) abs( $args['offset'] );
      
    }
    
    if( $start < 0 ) {
      
      $start      = 0;
      
    }
    
    // Max
    $max          = isset( $args['max'] )         ? (int) abs( $args['max'] )               : 10;
    
    if( $max < 1 ) {
      
      $max        = 1;
      
    }
    else if( $max > 10 ) {
      
      $max        = 10;
      
    }

    $showTitle    = ( isset( $args['showvideotitle'] ) && ( $args['showvideotitle'] === true || $args['showvideotitle'] == 'true' || (int) $args['showvideotitle'] == 1 || $args['showvideotitle'] == 'on' ) ) ? true : false;

    $showDescr    = ( isset( $args['showvideodescription'] ) && ( $args['showvideodescription'] === true || $args['showvideodescription'] == 'true' || (int) $args['showvideodescription'] == 1 || $args['showvideodescription'] == 'on' ) ) ? true : false;
    
    $exact        = ( isset( $args['exact'] ) && ( $args['exact'] === true || $args['exact'] == 'true' || (int) $args['exact'] == 1 || $args['exact'] == 'on' ) ) ? true : false;

    $apiVersion   = isset( $args['apiVersion'] )  ? (int) abs( $args['apiVersion'] )        : 2;

    $width        = isset( $args['width'] )       ? (int) abs( $args['width'] )             : 0; // The default width should be specified in the calling environment (widget or shortode)

    $height       = isset( $args['height'] )      ? (int) abs( $args['height'] )            : 0; // The default height should be specified in the calling environment (widget or shortode)
    
    $duration     = ( isset( $args['duration'] ) && preg_match( '#^(short|medium|long)$#i', trim( $args['duration']  ) ) ) ? trim( strtolower( $args['duration'] ) ) : '';

    $class        = isset( $args['class'] )       ? strip_tags( $args['class'] )   	        : '';
    
    $id           = isset( $args['id'] )          ? strip_tags( $args['id'] )               : '';
    
    $relation     = isset( $args['relation'] )    ? strtolower( $args['relation'] )         : '';

    $wpSearch     = ( isset( $args['wpSearch'] ) && $args['wpSearch'] == true ) ? true      : false;  // Will only have an effect on the search results page
    
    $preview      = ( isset( $args['preview'] ) && ( ( $args['preview'] === true || strtolower( $args['preview'] ) === 'true' || (int) $args['preview'] == 1 || $args['preview'] == 'on' ) ) ) ? true : false;
    
    $autoplay     = ( isset( $args['autoplay'] ) && ( ( $args['autoplay'] === true || strtolower( $args['autoplay'] ) === 'true' ) || (int) $args['autoplay'] === 1 || strtolower( $args['autoplay'] ) === 'on' ) ) ? true : '';

    // Random pool
    $random       = ( isset( $args['random'] ) )    ? (int) abs( $args['random'] )            : $max;
  
    if( $random < $max ) {
      
      $random = $max;
      
    }

    $lang         = ( isset( $args['lang'] ) && preg_match( '#^[a-z]{2}$#i', $args['lang'] ) ) ? strtolower( $args['lang'] ) : '';

    $region       = ( isset( $args['region'] ) && preg_match( '#^[a-z]{2}$#i', $args['region'] ) ) ? strtoupper( $args['region'] ) : '';

    $author       = ( isset( $args['author'] ) ) ? trim( $args['author'] ) : '';

    $viewRelated  = ( isset( $args['viewrelated'] ) && ( (int) $args['viewrelated'] === 0 || strtolower( $args['viewrelated'] ) === 'false' || strtolower( $args['viewrelated'] ) === 'no' ) ) ? 0 : 1;
    
    if( isset( $args['viewrelated'] ) && ( strtolower( $args['viewrelated'] ) === 'yes' || strtolower( $args['viewrelated'] ) === 'true' ) ) {
      
      $viewRelated = 1;
      
    }

    /**
     * Depending on what relation has been specified generate the proper keywords for searching YouTube.
     */
    if( $relation !== 'posttags' && $relation !== 'keywords' && $relation !== 'postcategories' ) {
      
      $relation   = 'posttitle';
      
    }

    // Relation: Post title.
    if( $relation === 'posttitle' ) {
      
      $search = $this->getPostTitle();
      
    }
    // Relation: Post tags.
    elseif( $relation == 'posttags' ) {
      
      $search  = $this->getPostTags();

    }
    // Relation: Post categories.
    elseif( $relation === 'postcategories' ) {

      $search = $this->getPostCategories();
      
    }
    // Relation: Custom Keywords.
    elseif( $relation === 'keywords' ) {
      
      $search     = trim( $searchTerms );
      
    }

    /**
     * You can add additional filtering paramets via the "filter" parameter.
     * The filter value will simply be added to the search terms.
     */
    $filter     = '';
    
    if( isset( $args['filter'] ) && !empty( $args['filter'] ) ) {
      
      $filter   = trim( strip_tags( $args['filter'] ) );
      
      // Use $tmp so $filter stays untouched an can be saved as widget option.
      $tmp      = preg_replace( '#\+posttitle#i', $this->getPostTitle(), $filter );
      
      $tmp      = preg_replace( '#\+posttags#i', $this->getPostTags(), $tmp );
        
      $tmp      = preg_replace( '#\+postcategories#i', $this->getPostCategories(), $tmp );
      
      // @changed 2014-10-25
      // +postMeta:key
      $postMetaPattern = '#\+postmeta:([a-z0-9-_]+)#i';

      if( preg_match_all( $postMetaPattern, $tmp, $match ) && isset( $match[1] ) && !empty( $match[1] ) && isset( $post->ID ) ) {
        
        $postMeta     = '';
        
        foreach( $match[1] as $postMetaKey ) {
          
          $postMeta .= get_post_meta( $post->ID, $postMetaKey, true );
          $postMeta .= ' ';

        }

      	$tmp  = preg_replace( $postMetaPattern, ' ', $tmp );
        
        $tmp .= ' ' . $postMeta;
    
      }

      $tmp      = trim( $tmp );

      $search   .= ' ' . $tmp;

    }

    $norm         = array(
      'title'                 => $title,
      'terms'                 => $searchTerms,
      'orderBy'               => $orderBy,
      'start'                 => $start,
      'max'                   => $max,
      'apiVersion'            => $apiVersion,
      'width'                 => $width,
      'height'                => $height,
      'class'                 => $class,
      'id'                    => $id,
      'relation'              => $relation,
      'search'                => $search,
      'wpSearch'              => $wpSearch,
      'exact'                 => $exact,
      'random'                => $random,
      'showvideotitle'        => $showTitle,
      'showvideodescription'  => $showDescr,
      'preview'               => $preview,
      'duration'              => $duration,
      'lang'                  => $lang,
      'region'                => $region,
      'author'                => $author,
      'filter'                => $filter,
      'viewrelated'           => $viewRelated,
      'autoplay'              => $autoplay
    );

    return $norm;

  }
  
  /**
   * Load external file/URL via cURL
   *
   * @param   string  $url    URL to be loaded.
   * @return  string          Response from the YT web service or a plain error message.
   */
  public function loadUrlVia_curl( $url ) {

    try {
    
      // Configure cURL
      $curl   = curl_init();

      curl_setopt( $curl, CURLOPT_URL, $url );
      
      // The YouTube search API is requires connecting via SSL/HTTPS which cURL needs to be configurated for.
      if( preg_match( '#^https#i', $url ) ) {
        
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );

        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
      
      }

      curl_setopt( $curl, CURLOPT_TIMEOUT, $this->timeout );

      curl_setopt( $curl, CURLOPT_FILETIME, true );

      curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );



      // Load the URL/response
      $data   = curl_exec( $curl );

      $error  = (int) curl_errno( $curl );
    

      curl_close( $curl );

var_dump( $data);Exit;die();return;
    

      if( $error !== 0 ) {
      
        return 'cURL error code: ' . $error;
      
      }
      else {
    
        return $data;
    
      }

    }
    catch( Exception $e ) {
    
      return 'cURL exception: ' . $e->getMessage();
    
    }

  }
  
  /**
   * Load external file/URL via fopen
   *
   * @param   string  $url    URL to be loaded.
   * @return  string          Response from the YT web service or a plain error message.
   */
  public function loadUrlVia_fopen( $url ) {
    
    if( preg_match( '#^https#i', $url ) && ( RYTV_METHOD === false ) ) {
      
      return '<!-- RelatedYouTubeVideos: Error: Cannot open HTTPS connection! Please install either curl or an HTTPS wrapper for the fopen function. -->';
      
    }

    $context  = stream_context_create(
      array(
        'http' =>
          array(
            'timeout' => $this->timeout
        )
      )
    );
    
    $data     = @file_get_contents( $url, false, $context );
    
    return ( $data === false ) ? 'Cannot reach YouTube Search API!' : $data;
    
  }
  
  /**
   * Get the (current) post title.
   * Use cached data if possible.
   *
   * @return string The post title (or an empty string).
   */
  public function getPostTitle() {
    
    global $post;
    
    if( !isset( $post->ID ) ) {
      
      return '';
      
    }
    
    if( isset( $this->meta[ $post->ID ]['title'] ) ) {
      
      return $this->meta[ $post->ID ]['title'];
      
    }
    else {

      $title = ( isset( $post->post_title ) ) ? $post->post_title : '';
      
      $this->meta[ $post->ID ]['title'] = $title;
      
      return $title;

    }

  }

  /**
   * Get a list of all tags for the current post.
   * Use cached data if possible instead of calling the database over and over again.
   *
   * @return string List of post tags.
   */
  public function getPostTags() {

    global $post;
    
    if( !isset( $post->ID ) ) {
      
      return '';
    
    }
    
    if( isset( $this->meta[ $post->ID ]['tags'] ) ) {
      
      return $this->meta[ $post->ID ]['tags'];
      
    }
    else {
    
      $list       = '';
    
      $tags     = wp_get_post_tags( $post->ID );
      
      foreach( $tags as $tag ) {
        
        $list   .= ' ' . $tag->name;
        
      }
      
      $list     = trim( $list );
      
      $this->meta[ $post->ID ]['tags'] = $list;
      
      return $list;
      
    }

  }

  /**
   * Get a list of all categories for the current post.
   * Use cached data if possible instead of calling the database over and over again.
   *
   * @return string List of all categories.
   */
  public function getPostCategories() {
    
    global $post;
    
    if( !isset( $post->ID ) ) {
      
      return '';
      
    }
    
    if( isset( $this->meta[ $post->ID ]['categories'] ) ) {
      
      return $this->meta[ $post->ID ]['categories'];
      
    }
    else {

      $list         = '';
    
      $categories = wp_get_post_categories( $post->ID );

      foreach( $categories as $category ) {
      
        $cat      = get_category( $category );
      
        $list     .= ' ' . $cat->name;
    
      }
    
      $list       = trim( $list );

      $this->meta[ $post->ID ]['categories'] = $list;

      return $list;

    }
  
  }

}
?>