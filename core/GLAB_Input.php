<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * G LAB Input Library for Code Igniter v2
 * Written by Ryan Brodkin
 * Copyright 2010
 */

// ADDED support for secure cookies
class GLAB_Input extends CI_Input { 
	
	function get ($str) {
		
		$ci_method = parent::get($str);
		
		if (!$ci_method) $glab_method = $this->qs($str);
		
		if ($glab_method === FALSE) return $ci_method;
		else return $glab_method;
	}
	
	function  qs ($str) {
		
		$qs = $this->server('REDIRECT_QUERY_STRING');
		$qs = explode('&',$qs);
		
		foreach ($qs as $part) {
			$part = explode('=',$part);
			$key = $part[0];
			if (isset($part[1])) $value = $part[1];
			else return null;
			
			if ( $key == $str ) return urldecode($value);
		}
		
		return FALSE;
	}
	
	function cookie($index = '', $xss_clean = FALSE)
	{
		return $this->_fetch_from_array($_COOKIE, $index, $xss_clean);
	}
	
	function set_cookie($name = '', $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure=FALSE) {
		if (is_array($name))
		{
			foreach (array('value', 'expire', 'domain', 'path', 'prefix', 'name', 'secure') as $item)
			{
				if (isset($name[$item]))
				{
					$$item = $name[$item];
				}
			}
		}

		if ($prefix == '' AND config_item('cookie_prefix') != '')
		{
			$prefix = config_item('cookie_prefix');
		}
		if ($domain == '' AND config_item('cookie_domain') != '')
		{
			$domain = config_item('cookie_domain');
		}
		if ($path == '/' AND config_item('cookie_path') != '/')
		{
			$path = config_item('cookie_path');
		}

		if ( ! is_numeric($expire))
		{
			$expire = time() - 86500;
		}
		else
		{
			if ($expire > 0)
			{
				$expire = time() + $expire;
			}
			else
			{
				$expire = 0;
			}
		}

		setcookie($prefix.$name, $value, $expire, $path, $domain, $secure);
	}
	
}