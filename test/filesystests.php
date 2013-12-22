<?php
/**
 * @file
 * Tests for the file system
 */

include '../filesys.inc';

class testFileSys extends PHPUnit_Framework_TestCase {
	public function setUp() {}
	public function tearDown() {}
	
	/* Write File tests */
	public function testWriteFile() {
		$filename = 'test.json';
		$filestream = 'maryhad';
		textus_put_file($filename, $filestream);
	}
	/* Get file tests */
	public function testGetFile() {
		$filename = 'test.json';
	    textus_get_file($filename);
	    
	}
}