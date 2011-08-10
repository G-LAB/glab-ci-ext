<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * G-LAB Access Control List (ACL) Library for Code Igniter v2
 * Written by Ryan Brodkin
 * Copyright 2010
 */

class ACL 
{

	private $CI;
	private $acl;
	
	function __construct () {
	
		$this->CI = $CI =& get_instance();
		
		$this->CI->load->library('session');
		$this->CI->load->helper('url');
		$this->CI->load->model('profile');
		
		// Send to Login if No Session
		if ($this->is_auth() !== true  && $this->CI->router->fetch_class() != 'login') 
		{
			redirect('login');
		}
		
		// Run ACL Check
		else 
		{
			
			require_once 'Zend/Acl.php';
			require_once 'Zend/Acl/Role.php';
			require_once 'Zend/Acl/Resource.php';
			
			// Load Up Zend ACL
			$this->acl = new Zend_Acl();
			/*
			// Set Roles
			$this->acl	->addRole(new Zend_Acl_Role('guest'))
						->addRole(new Zend_Acl_Role('client'), 'guest')
						->addRole(new Zend_Acl_Role('employee'))
						->addRole(new Zend_Acl_Role('administrator'), 'employee');
			
			// Set Resources
			// Get Controllers for this App
			$controllers = glob(APPPATH.'controllers/*.php');
			foreach ($controllers as $controller) 
				$this->acl->add(new Zend_Acl_Resource( $this->CI->config->item('app_name').'_'.basename($controller,EXT) ));
				
			$this->acl->add(new Zend_Acl_Resource( $this->CI->config->item('app_name').'_domain_names' ));
			$this->acl->add(new Zend_Acl_Resource( $this->CI->config->item('app_name').'_web_hosting' ));
			$this->acl->add(new Zend_Acl_Resource( $this->CI->config->item('app_name').'_cortex' ));
			
			// Set User Roles
			$parent[] = 'guest';
			
			if ($eid) $parent[] = 'client';
			
			if ($eid) $employee = $this->CI->db->get_where('entities_admin','eid = '.$eid);
			if ($eid && $employee->num_rows() == 1) {
				$parent[] = 'employee';
			}
			
			if ($eid == 1) $parent[] = 'administrator';
			
			
			$this->acl->addRole(new Zend_Acl_Role("$eid"), $parent);
			
			// PERMISSIONS
			// Deny All
			//$this->acl->deny();
			
			foreach ($whitelist as $wl) $this->acl->allow('guest', 'cms_'.$wl);
			$this->acl->allow('employee');*/
			
			// Validate Login
			$error_msg = "<div class=\"error msg\">Sorry, you do not have permission to access this page.</div>";
			$error_msg.= "<p>If you feel this message is in error, please contact us at 1.877.620.GLAB.</p>";
			if ($this->is_permitted() !== true) show_error($error_msg,403);
		
		}
		
	}
	
	function create_session ($pid) {
		$CI =& get_instance();
		$CI->load->library('session');
		$CI->session->set_userdata('pid', $pid);
		return TRUE;
	}
	
	function get_pid () {
		return $this->CI->session->userdata('pid');
	}
	
	function is_auth() { 
		return ($this->get_pid()) ? true : false;
	}
	
	function is_permitted($resource=false,$profile=false) {
		if ($this->is_auth() == true  || $this->CI->router->fetch_class() == 'login') return true;
		
		/*if (!$resource) $resource = $this->CI->config->item('app_name').'_'.$this->CI->router->fetch_class();
		
		if (!$eid) $eid = $this->CI->entity->getEid();
		
		if ($this->acl->has("$resource")) return $this->acl->isAllowed("$eid", "$resource" );
		else return FALSE;*/
		
	}
	
}
	
// End of file.