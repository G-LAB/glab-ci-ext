<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require '/nfs/c06/h05/mnt/91576/data/lib/CI-Custom/libraries/S3.php';

/**
 * G LAB Amazon S3 Backup Library for Code Igniter
 * Written by Ryan Brodkin
 * Copyright 2010
 */

class S3_Backup extends S3 {
	
	private $backupBucket = 'GLAB-CMS-Backups';
	
	function __construct () {
		
		parent::__construct();
		
		$this->setAuth('AKIAJLYGZJSNFJEAGSSA','R0gFPVPW3C8Ce3bbI490K57xLqEpQF1NKy1DW6gr');
	}
	
	function create ($localpath,$remotepath) {
		
		$localpath = realpath($localpath);
		
		if (S3::putObject(S3::inputFile($localpath), $this->backupBucket, $remotepath, S3::ACL_PRIVATE)) return TRUE;
		else return FALSE;
	}
	
	function rotate () {
		$s3 = S3::getBucket($this->backupBucket);
		
		// Organize Backups Chronologically
		foreach ($s3 as $s3_filename => $data) {
			
			$filename  = substr($s3_filename, 0, strpos($s3_filename,'.'));
			
			$backupgroup = substr($filename, 0, (strlen($filename) - 25) );
			$timestamp  = strtotime(substr($filename, -24));
			
			$backups[$backupgroup][date('Y', $timestamp)][date('n', $timestamp)][date('j', $timestamp)][date('G', $timestamp)][] = $s3_filename;
			
		}
		
		// Get Expired Children of Each Backup Group
		$expired = array();
		foreach ($backups as $backupgroup => $data) {
			array_merge($this->getExpired(), $expired);
		}
		
		
		//print_r($this->getChildren($backups));
	}

	private function getChildren ($data=array()) {
		$output = array();
		array_walk_recursive($data, create_function('$val, $key, $obj', 'array_push($obj, $val);'), &$output);
		return $output;
	}

}

?>