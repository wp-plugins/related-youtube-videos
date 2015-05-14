<?php
/**
 * YouTube API v3.
 *
 * @package     relatedYouTubeVideos
 * @copyright   Copyright (c) 2015 http://www.meomundo.com
 * @author      Christian Doerr <doerr@meomundo.com>
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPL-2.0)
 *
 */
class RelatedYouTubeVideos_Youtube {
  
  
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
  
  protected $Youtube;

  /**
   * The Constructor
   */
  public function __construct( $apiKey ) {
    
    $this->apiKey = $apiKey;
    
    $max_execution_time = (int) ini_get('max_execution_time');
    
    $this->timeout      = ( $max_execution_time > 0 ) ? ( $max_execution_time - 5 ) : 15;

    $client = new Google_Client();

    $client->setDeveloperKey( $this->apiKey );

    // Define an object that will be used to make all API requests.
    $this->Youtube  = new Google_Service_YouTube( $client );

  }
  
  public function search( $config ) {

    $request = array();
    
    $request['type'] = 'video';
    
    // mapping v2 parameters to v3 parameters
    // @todo https://developers.google.com/youtube/v3/docs/search/list
    $request['maxResults']          = ( isset( $config['max-results'] ) ) ? (int) $config['max-results'] : 1;

    if( $request['maxResults'] < 1 ) {
      
      $request['maxResults']        = 1;
      
    }

    if( isset( $config['start-index'] ) && $config['start-index'] > 1 ) {
      
      $request['maxResults'] += $config['start-index'];
      
      
    }

    $request['q']                   = ( isset( $config['q'] ) )           ? $config['q']: '';

    /**
     * The following parameters are OPTIONAL
     * and it's better to not include them than sending them with empty values
     * for some reason?!
     */
    if( isset( $config['duration'] ) ) {
      
      $request['videoDuration']     = $config['duration'];
    
    }
    
    // language
    // @todo why is this not working?!
    /*
    if( isset( $config['lr'] ) ) {
      $request['relevanceLanguage'] = $config['lr'];
    }
    */
    
    // region/country
    if( isset( $config['region'] ) ) {
      
      $request['regionCode']        = $config['region'];
    
    }

    if( isset( $config['orderby'] ) ) {
    
      // this is a workaround to match older shortcodes or widgets that were created under the API v2!
      $request['order']             = ( strtolower( $config['orderby'] ) === 'published' ) ? 'date' : $config['orderby'];
    
    }
    
    if( isset( $config['author'] ) ) {
      
      $request['channelId']         = $config['author'];
      
    }

    // @todo still unclear?!
    //  author => channelID // playlist ID ???

    
    $errorMsg = '';
  
    try {
      
      $searchResponse = $this->Youtube->search->listSearch(
        'id,snippet',
        $request
      );
      
  	}
    catch( Google_ServiceException $e ) {
      
      $errorMsg .= sprintf( '<!-- RelatedYoutubeVideos Error: A service error occurred: %s -->', htmlspecialchars( $e->getMessage() ) );
 
    }
    catch( Google_Exception $e ) {

      $errorMsg .= sprintf( '<!-- RelatedYoutubeVideos Error: A client error occurred: %s -->', htmlspecialchars( $e->getMessage() ) );
    
    }

    if( $errorMsg !== '' ) {
      
      echo $errorMsg;
      
    }
    else {
    
      return $searchResponse;

    }

  }
  
  public function extractVideos( $searchResponse, $offset = 1 ) {

    $videos = array();
    
    if( !isset( $searchResponse['items'] ) || empty( $searchResponse['items'] ) ) {
      
      return $videos;

    }
    
    $counter  = 0;
    
    $dimItems = count( $searchResponse['items'] );

    foreach( $searchResponse['items'] as $item ) {
      
      $counter++;
      
      if( $offset > 1 && ( $counter < $offset || $counter === $dimItems ) ) {
        
        continue;
        
      }
      
      $videoID      = isset( $item['id']['videoId'] )     ? $item['id']['videoId'] : '';
      $title        = isset( $item['snippet']['title'] )       ? $item['snippet']['title'] : '';
      $description  = isset( $item['snippet']['description'] ) ? $item['snippet']['description'] : '';
      $thumbnail    = 'http://img.youtube.com/vi/' . $videoID . '/0.jpg';
      
      if( $videoID !== '' ) {
        
        $videos[] = array(
          'videoID' => $videoID,
          'title'   => $title,
          'description' => $description,
          'thumbnail'   => $thumbnail
        );
        
      }
      
    }
    
    return $videos;

  }

}
?>