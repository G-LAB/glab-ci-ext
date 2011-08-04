<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * G-LAB Yelp Library for Code Igniter v2
 * Written by Ryan Brodkin
 * Copyright 2011
 */

class Yelp
{ 
	
	private $auth_key;
	
	function __construct ($auth_key=false) {
		
		// Load Codeigniter, If Available
		if (function_exists('get_instance')) {
			$CI = &get_instance();
			$CI->load->config('auth');
		}
		
		if (isset($CI) AND $CI->config->item('auth_yelp_key')) $this->auth_key = $CI->config->item('auth_yelp_key');
		elseif ($auth_key) $this->auth_key = $auth_key;
		else trigger_error('No Yelp API key passed to library.');
	
	}
	
	function get_business_by_phone ($phone) {		
		
		// Strip Non-Numeric Characters
		$phone = preg_replace('/\D/', '', $phone);
		
		$request['phone'] = $phone;
		
		$data = $this->_request('phone_search',$request);
		
		if (isset($data->businesses[0])) return $data->businesses[0];
		else return false;
	}
	
	private function _request ($method,$params) {
		
		$params['ywsid'] = $this->auth_key;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://api.yelp.com/'.$method.'?'.http_build_query($params));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'G LAB Studios/Yelp Library v1'); 
		$data = curl_exec($ch);
		curl_close($ch);
		
		// Decode JSON Data If More Than 100 Characters
		if (strlen($data) > 100) $data = json_decode($data);
		
		if (isset($data->message->text) AND $data->message->text == "OK") return $data;
		else return false;
	}
	
}