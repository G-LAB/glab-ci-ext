<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * G-LAB Twitter Library for Code Igniter v2
 * Written by Ryan Brodkin
 * Copyright 2011
 */

class Twitter {

	private $CI;
	
	private $api;
	
	private $consumer_key;
	private $consumer_secret;
	private $user_token;
	private $user_secret;
	
	function __construct () {
		
		$this->CI =& get_instance();
		
		$this->CI->config->item('auth_twitter_consumer_key');
		
		if (
			!$this->CI->config->item('auth_twitter_consumer_key') OR
			!$this->CI->config->item('auth_twitter_consumer_secret') OR
			!$this->CI->config->item('auth_twitter_user_token') OR
			!$this->CI->config->item('auth_twitter_user_secret')
		) {
			trigger_error('Cannot connect to Twitter.  Auth keys unavailable.');
		} 
		
		require_once 'twitteroauth.php';
		$this->api = new TwitterOAuth(
			$this->CI->config->item('auth_twitter_consumer_key'),
			$this->CI->config->item('auth_twitter_consumer_secret'),
			$this->CI->config->item('auth_twitter_user_token'),
			$this->CI->config->item('auth_twitter_user_secret')
		);
	}
	
	function get_status ($screen_name) {
		$data = $this->api->get('statuses/user_timeline');
		if (count($data)) return $data[0];
	}
	
	function tweet ($status) {
		
		$tweet['status']  = $status;
		
		return $this->api->post('statuses/update', $tweet);
	}
	
	function home_timeline () {
		return $this->api->get('statuses/home_timeline');
	}
}