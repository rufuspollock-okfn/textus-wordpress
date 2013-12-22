<?php
/**
 * Functions that wrap around Elastic Search
 * 
 * Owes a debt to http://hublog.hubmed.org/archives/001907.html
 */
include 'es_search.php';

class dao {

	const ES_HOST = 'http://localhost';
	const ES_PORT = '9200';
	const ES_COLLECTION = 'textus';
	
	protected static $defaults = array(
		'host' => dao::ES_HOST,
		'port' => dao::ES_PORT,
		'collection' => dao::ES_COLLECTION
	);
	
	public function __construct() 
	{
	}
	
	/**
	 * Private function to make the call. 
	 * It is up to the functions to decode the JSON
	 * @param string $url - the url 
	 * @param array $http - http array used in the stream context
	 * @return string
	 */
	private function _call($url, $http = array()) 
	{
		if (!$url) {
			print "No url has been given.";
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST,strtoupper($http['method']));//set the header for the call
		
		if (array_key_exists('content', $http)) {
		   curl_setopt($ch, CURLOPT_POSTFIELDS, $http['content']);
		} 
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$response = curl_exec($ch);

		if (!curl_errno($ch)) {
			$json = json_decode($response, TRUE);
		} else {
			print curl_error($ch);
		}
		return $response;
	}
	
	
	/**
	 * Function to put the text into the collection
	 * Wrapper around the PUT operation
	 * @param string $filename - the filename of the entity
	 */
	public function create ($filename, $content) 
	{
		$defaults = self::$defaults;
        $http = array('method'=>'PUT', 
        		'content'=> json_encode($content),
        		'header'=> 'Content-Type:application/json',
        );
        $url = $defaults['host'].':'.$defaults['port'].'/'.$defaults['collection'].'/'.$filename.'/_create';
		print $url;
        return $this->_call($url, $http);
	}

	/**
	 * Function to get a given filename
	 * Builds the collection name and then append _search to the URL
	 * @param string $filename - the filename of the entity
	 * @return string
	 */
	public function retrieve ($filename) 
	{
		$defaults = self::$defaults;
		$http = array('method'=>'GET');
		$url = $defaults['host'].':'.$defaults['port'].'/'.$defaults['collection'].'/'.$filename.'/_search';
		return $this->_call($url, $http);
	}
	
	/**
	 * Function to delete a given entity
	 * @param unknown $filename - the filename of the entity
	 * @return string
	 */
	public function delete ($filename) 
	{
		$defaults = self::$defaults;
		$http = array('method'=>'DELETE');
		$url = $defaults['host'].':'.$defaults['port'].'/'.$defaults['collection'].'/'.$filename;
		return $this->_call($url, $http);
	}
	
	/**
	 * Function to build the search array and call the db
	 * @todo add in facet to the search
	 * @param unknown $query
	 * @param unknown $filter
	 */
	public function search ($query, $filter) 
	{
		$defaults = self::$defaults;
		$search = new ES_Search($query, $filter);
		$searchterm = $search->search();
		$http = array('method'=>'GET');
		$url = $defaults['host'].':'.$defaults['port'].'/'.$defaults['collection'].'/_search';
	
		return $this->_call($url, $http);
	}

}