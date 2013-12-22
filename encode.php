<?php
/**
 * A set of functions to deal with encoding a url so
 * that it is FS 
 */

class encodingFuncs {
	
	/**
	 * Function to encode a string into 2 character hex string
	 * @param string $id
	 * @return returns a hexadecimal character
	 */
	function char2hex($id) {
		return chr(hexdec($id));
	}
	
	/**
	 * Function to convert a 2 character hex character to decimal
	 * @param string $id
	 * @return returns a decimal string converted from hexadecimal
	 */
	function hex2char($id) {
		return '^'.sprintf("%02x",ord($id));
	}
	/**
	 * Function to encode the filename to deal with some potential issues
	 * in storing the texts
	 * 
	 * Derived from the Pairtree spec
	 * 
	 * @param string $id - the filename to be encoded
	 * @return string - encoded filename
	 */
	function encodeFileName($id) {
		//first pass: utf8 encode the string
		$encode = utf8_encode(html_entity_decode($id));
		$encode = str_replace(' ', '_', $encode);
		$newid = preg_replace('/["\*\+,<=>\?\\\^\|]|[^!-~]/u', self::hex2char($encode), $encode);
		//second pass: make anything that gives the filesystem issues into hexadecimal
		$second_pass_m = array(':'=>'+', '.'=>',', DIRECTORY_SEPARATOR=>'=', ' '=>'_');
		
		$arr = str_split($newid);
		$second_pass = array();
		foreach ($arr as $chr) {
			if (array_key_exists($chr, $second_pass_m)) {
				$second_pass[] = $second_pass_m[$chr];
			} else {
				$second_pass[] = $chr;
			}
		}
		
		return join("", $second_pass);
	}
	
	/**
	 * Function to decode the filename
	 * 
	 * @param string $filename - file name of
	 */
	function decodeFileName($filename) {
		$second_pass_m = array('+'=>':', ','=>'.', '='=>DIRECTORY_SEPARATOR ,'_'=> ' ');
		
		$arr = str_split($id);
		$second_pass = array();
		foreach ($arr as $chr) {
			if (array_key_exists($chr, $second_pass_m)) {
				$second_pass[] = $second_pass_m[$chr];
			} else {
				$second_pass[] = $chr;
			}
		}
		$dec_id = join("", $second_pass);
		$ppath = preg_replace('/\^(..)/', self::char2hex($dec_id), $dec_id);
		return $ppath;
	}
}