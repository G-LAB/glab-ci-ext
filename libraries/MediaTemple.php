<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * G-LAB Media Temple Library for Code Igniter v2
 * Written by Ryan Brodkin
 * Copyright 2011
 */

class MediaTemple {

	private $key;
	
	function __construct () {
		
		$this->CI =& get_instance();
		
		$this->key = $this->CI->config->item('auth_mediatemple_key');
	}
	
	function get_service ($id=null) {
		return $this->_request('services/'.$id);
	}
	
	private function _request($method,$data=array()) {
		
		// Define Global Parameters
		$params['apikey'] = $this->key;
		$params['prettyPrint'] = 'true';
		$params['wrapRoot'] = 'false';
		
		// Append Global Parameters
		$data = array_merge($data, $params);
		
		// Fetch Data
		$api = API_Request('GET','https://api.mediatemple.net/api/v1/'.$method,$data);
		
		// On Success, Decode JSON Into Array
		if ($api) $api = json_decode($api, true);
		
		//Return Data
		if (isset($api->errors)) return false;
		else return $api;
	}
}