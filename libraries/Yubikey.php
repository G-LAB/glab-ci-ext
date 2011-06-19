<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * G-LAB Yubikey Library for Code Igniter v2
 * Written by Ryan Brodkin
 * Copyright 2010
 */

class Yubikey 
{
	
	public $status;
	
	function validate ($ykid) {
		$CI =& get_instance();
		$CI->load->helper(array('glab_api','array'));
		
		$params['id'] = 3468;
		$params['otp'] = $ykid;
		
		$result['status'] = 'Server did not respond or response empty.';
		$api = API_Request('GET','http://api.yubico.com/wsapi/verify',$params);
		
		foreach (explode("\n", $api) as $pair) if (preg_match('/([a-z]+)=/i', $pair, $chunk)) {
				$key = $chunk[1];
				$value = trim(substr($pair, strlen($key)+1));
				$result[$key] = $value;
		}
		
		$this->status = element('status',$result);
		
		if (element('status',$result) == 'OK') return TRUE;
		else return FALSE;
	}
	
}
	
// End of file.
