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

	private $permissions = array();

	public function __construct ($permissions)
	{
		$this->CI =& get_instance();

		$this->CI->load->library('session');
		$this->CI->load->helper('url');
		$this->CI->load->model('profile');

		if (is_array($permissions) === true)
		{
			$this->permissions = $permissions;
		}
	}

	public function allow ($role, $resource, $action=false)
	{
		$role = $this->process_role($role);

		if ($action == true)
		{
			$this->allow($role, $resource, false);
			$this->permissions[$role][$resource][] = $action;
			log_message('debug','ACL: Allow '.$role.' to perform '.$action.' action on '.$resource);
		}
		elseif (isset($this->permissions[$role][$resource]) !== true)
		{
			$this->permissions[$role][$resource] = array();
			log_message('debug','ACL: Allow '.$role.' access to '.$resource);
		}
	}

	public function create_session ($pid)
	{
		$CI =& get_instance();
		$CI->load->library('session');
		$CI->session->set_userdata('pid', $pid);

		return TRUE;
	}

	public function get_pid ()
	{
		return $this->CI->session->userdata('pid');
	}

	public function is_auth()
	{
		return ($this->get_pid()) ? true : false;
	}

	public function is_allowed ($resource, $action=false, $role=false)
	{
		// Assume current user if no role specified
		if ($role == false)
		{
			$role = $this->get_pid();

			// Default to guest group if no PID
			if ($role != true)
			{
				$role = ':guest';
			}
		}

		$role = $this->process_role($role);

		$result = $this->process_acl($resource, $action, $role);

		if ($result === true)
		{
			log_message('info','ACL: Permission granted to '.$role.' accessing '.$resource);
			return true;
		}
		else
		{
			log_message('debug','ACL: Permission denied to '.$role.' accessing '.$resource);
			return false;
		}
	}

	public function process_acl ($resource, $action, $role)
	{
		log_message('info','ACL: Test if '.$role.' can access '.$resource);

		// Check if direct match for requested role without action
		if (isset($this->permissions[$role][$resource]) === true  && $action == false)
		{
			return true;
		}
		// Check if direct match for requested role with action
		elseif (isset($this->permissions[$role][$resource]) === true  && in_array($action, $this->permissions[$role][$resource]) === true)
		{
			return true;
		}
		// If role exists, check member groups
		elseif (isset($this->permissions[$role]) === true)
		{
			// Check groups granted by inhertiance
			foreach ($this->permissions[$role] as $group=>$actions)
			{
				// Check if resource is a group id
				if (substr($group, 0, 1) == ':')
				{
					$result = $this->process_acl($resource,$action,$group);

					if ($result == true)
					{
						return true;
					}
				}
			}
		}
		else
		{
			log_message('debug','ACL: Role '.$role.' does not exist');
			return false;
		}
	}

	private function process_role ($str)
	{
		// User and Group
		if (strpos($str,':') !== false)
		{
			return $str;
		}
		// User Member of Own Group
		else
		{
			return $str.':'.$str;
		}
	}

	public function require_ssl ()
	{
		if(isset($_SERVER['HTTPS']) === false OR $_SERVER['HTTPS'] != 'on')
		{
			log_message('ACL: Redirecting to SSL');
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: https://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
			exit();
		}
	}

}

// End of file.