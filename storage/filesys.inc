<?php 
/**
 * @file
 * Deals with File System operations that the server requires
 * 
 * we need to wrap around the WP File System functions
 */

define( FS_METHOD, 'direct'); //not great

include_once ABSPATH . 'wp-admin/includes/file.php';

//credentials check for WP - need to see how Textus deals with it
//should probably be a system user
//need a form to set the system user. do this in the theme not here.

class FS {
	/**
	* Function to check the credentials
	* Checks that the system has a user and is allowed to access the file system
	* @return boolean
	*/
	function ol_check_credentials () {
		//this url needs to set in theme
		//$url = wp_nonce_url('themes.php?page=example','example-theme-options');
		//return request_filesystem_credentials($url, '', false, false, null);
		return true;
	}
	
	/**
	 * Function to return the upload directory details.
	 * Return the root so that we don't have to search for the file 
	 * at a later stage as WP uses month to search for the 
	 * @return string 
	 */
	function ol_get_upload_directory() {
		$upload_dir = wp_upload_dir();
	
		if (! $upload_dir['basedir']) {
			$error = $upload_dir['error'];
			return "{ error : $error }";
		}
		return trailingslashit($upload_dir['basedir']);
	}
	/**
	* Function to get the file. 
	* Generic function to be used 
	* @param string $filename
	* @return Array
	*    Either returns an error array to be encoded or the
	*    contents of the file associated with text
	*/
	function ol_get_file ($filename) {
		//check credentials
		/**if ( ! WP_Filesystem(check_credentials()) ) {
			//this ought to return an error
			return true;
		}*/
		//if pass, then calls the wp_filesystem global to run operation
		global $wp_filesystem;
		//get the real path and the file system
		$fname = self::ol_get_upload_directory().$filename . '.json';
		$writable = WP_Filesystem( FALSE, 'direct' );
		/*if (! $writable) {
			return "{ error : This filename cannot be loaded }";
		}*/
	
		$file = $wp_filesystem->get_contents( $fname );
		if ( !$file ) {
			return array('error' => 'The file does not exist');
		} else {
			return array( 'text' => $file);
		}
		//@todo needs error handling - return http error and write to log
	}
	
	/**
	 * Function to write the file
	 * @param string $filename - file identifier
	 * @param string $filestream - contents to be written to the file
	 * @return string file
	 */
	function ol_put_file ($author, $filename, $filestream) {
		//check credentials
		/*if ( ! WP_Filesystem(check_credentials()) ) {
			//this ought to return a http error
		}*/
		//if pass, then calls the wp_filesystem global to run operation
		global $wp_filesystem;
		//get the real path and the file system
		///check that the top directory exists
		//if not, create it
	    if (!is_dir(self::ol_get_upload_directory().$author)) {
	    	mkdir(self::ol_get_upload_directory().$author);
	    }
		$fname = self::ol_get_upload_directory().$author .DIRECTORY_SEPARATOR.$filename . '.json';
	
		$writable = WP_Filesystem( true, 'direct' );
		//write to the stored directory name.
		$file = $wp_filesystem->put_contents(
				$fname,
				$filestream,
				FS_CHMOD_FILE // predefined mode settings for WP files
		);
		if (!$file) {
			return '{ error : There has been an error storing the file }';
		} else {
			//send back the URL to call the file from
			return '{ success : The url is '.$author .DIRECTORY_SEPARATOR.$filename.'}';
		}
		//@todo needs error handling - return http error and write to log
	}

}

?>