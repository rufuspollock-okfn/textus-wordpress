<?php
/**
 * @file
 * Controller to deal with get actions
 */

include __DIR__.'/../encode.php';
include __DIR__.'/../storage/filesys.inc';

class get_text_controller {
	
	public function __construct() {}
	
	/**
	 * Function that takes the parameters and returns the data 
	 * using the correct response.
	 * 
	 * @param Array $incomingdata
	 *    Array of the incoming parameters
	 * @return Array
	 *    String of the variables for the view
	 *
	 */
	public function ol_get_text ($incomingdata, $type) {
		$response = array();

		$explodeincoming = explode('/', $incomingdata);
                $fs = new FS();
		return ($type == "text") ? self::ol_get_complete_text($incomingdata, $fs)
                    : self::ol_get_text_markup($incomingdata, $fs);
	}
	
	/**
	 * Function to take the coming string and return the correct
	 * response to the view
	 * 
	 * @param Array @incomingdata
	 *    Array of the incoming parameters 
	 *    These should include the text or the searchTerm
	 * @return string
	 *    JSON string is returned to the view
	 */
	function ol_parse_data($incomingdata) {
		$response = '';
		// If _GET['text'], get the
		if ($incomingdata['text']) {
		  $textid = parseParameters();
		
		  $response = ol_get_complete_text($textid);
		} elseif ($incomingdata['searchTerm']) {
		  
				
		  $search = new dao();
			//we need to get the search terms
		  $query = $incomingdata['searchTerm'];
		  $filter = '';
		  $response = $search->search($query, $filter);
		}
		return $response;
	}
	

	/**
	 * Function to get the text from the store.
	 * Returns the marked up JSON text.
	 *
	 * @param string textid - the text id to signify the
	 * @return string - JSON encoded text string
	 */
	function ol_get_complete_text($textid, $fs)
	{
		
		//need some error handling
	    
		if ($textid) {
			// If the textid isn't empty, then get the complete file.
			return $fs->ol_get_file($textid.'-text.txt');
		}
	}

	/**
	 * Function to get the text from the store.
	 * Returns the marked up JSON text.
	 *
	 * @param string textid - the text id to signify the
	 * @return string - JSON encoded text string
	 */
	function ol_get_text_markup($textid, $fs)
	{
		//need some error handling
		if ($textid) {
			// If the textid isn't empty, then get the complete file.
			return $fs->ol_get_file($textid.'-typography.txt');
		}
	}
}
