<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * G LAB Exception Handler for Code Igniter v2
 * Written by Ryan Brodkin
 * Copyright 2010
 */

class GLAB_Exceptions extends CI_Exceptions { 
	
	function log_exception($severity, $message, $filepath, $line) {
		
		$CI=&get_instance();
		
		$CI->load->library('email');
        $CI->email->from('noreply@glabstudios.com', 'G LAB Studios');
        $CI->email->to('ryan@glabstudios.com');

        $CI->email->subject('System Error Encountered in '.$filepath);
        $CI->email->message('Severity: '.$severity.'  --> '.$message. ' '.$filepath.' '.$line);

        $CI->email->send();
        $CI->email->print_debugger();
		
		parent::log_exception($severity, $message, $filepath, $line);
		
	}
	
}