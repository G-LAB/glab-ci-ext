<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * G LAB Exception Handler for Code Igniter v2
 * Written by Ryan Brodkin
 * Copyright 2010
 */

class GLAB_Exceptions extends CI_Exceptions { 
	
	private $errors = array();
	
	function log_exception($severity, $message, $filepath, $line) {
		
		$this->errors[] = 	array(	'severity'=>$severity,
									'message'=>$message,
									'filepath'=>$filepath,
									'line'=>$line
							);
		
		parent::log_exception($severity, $message, $filepath, $line);
		
	}
	
	function __destruct () {
		
		if (count($this->errors) > 0) {
		
			$CI=&get_instance();
			
			$email_message = "SUMMARY OF ERRORS\n";
			foreach ($this->errors as $error) $email_message .= "\n   - ".$error['message']." IN ".$error['filepath']." ON LINE ".$error['line']."(".$error['severity'].")";
			$email_message .= "\n\n\n";
			$email_message .= "CODEIGNITER INFORMATION\n";
			$email_message .= "\nCI Version: ".CI_VERSION;
			$email_message .= "\nController: ".$CI->router->fetch_class();
			$email_message .= "\nMethod: ".$CI->router->fetch_method();
			$email_message .= "\nURL: ".current_url();
			$email_message .= "\n\n\n";
			$email_message .= "PHP INFORMATION\n";
			$email_message .= "\nPHP Version: ".phpversion();
			
			$CI->load->library('email');
	        $CI->email->from('noreply@glabstudios.com', 'G LAB Studios');
	        $CI->email->to('ryan@glabstudios.com');
	
	        $CI->email->subject('System Error Encountered at '.current_url());
	        $CI->email->message($email_message);
	
	        $CI->email->send();
	        $CI->email->print_debugger();
		}
			
		parent::__destruct();
		
	}
}