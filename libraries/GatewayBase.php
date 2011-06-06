<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * G-LAB Gateway Base Library for Code Igniter v2
 * Written by Ryan Brodkin
 * Copyright 2011
 * 
 * THIS CLASS SHOULD NEVER BE INVOKED DIRECTLY!!!
 * CLASS IS DESIGNED TO BE EXTENDED BY GATEWAY LIBRARIES
 *
 */


class GatewayBase {
	
	protected $CI;
	protected $billingman;
	
	function __construct ($init=array()) {
		$this->CI = $CI =& get_instance();
		$this->billingman = $this->CI->load->model('billingman');
		$this->gateway_name = substr(get_class($this), 7);
		$this->gateway_profile = $init;
		$this->table_name = 'billing_paygateway_'.strtolower($this->gateway_name);
	}
	
	function _cron () {
		$this->_output($this->gateway_name.' does not have a cron method.');
	}
	
	function get_batch_errors () {
		return array();
	}
}
	
// End of file.