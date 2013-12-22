<?php
/**
 * Function to create the search JSON
 */

class ES_Search {
	
	/**
	 * constructor for ES
	 * @param string $querystr - the string to be searched
	 * @param array $filter - key value to create filter from
	 */
	public function __construct ($querystr, $filter) {
		$this->query = $querystr;
		$this->filter = $filter;
	}
	
	/**
	 * Function to build the search array for JSON encoding
	 */
	public function search () {
		$query = array(
			'query' => array (
				'filtered'=> array(
					'query' => array(
						'query_string' => array('query'=>$this->query)
					), 
					'filter' => array(
						'term'=> $this->filter
					)
				)	
			)	
		);
		return json_encode($query);
	} 
}