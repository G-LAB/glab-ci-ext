<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * G-LAB Asset Loader for Code Igniter v2
 * Written by Ryan Brodkin
 * Copyright 2011
 */

abstract class Asset_loader extends CI_Controller
{
	function __construct()
	{
		parent::__construct();

		$this->load->library('uri');
		$this->load->helper('file');

		$segments = $this->uri->segment_array();
		array_shift($segments);

		$path = APPPATH.'../assets';

		foreach ($segments as $segment)
		{
			$path.= '/'.$segment;
		}

		if (realpath($path) !== false)
		{
			$data = read_file($path);

			if (php_sapi_name() == 'apache2handler' || php_sapi_name() == 'apache')
			{
			    $headers = apache_request_headers();

			    if (isset($headers['If-Modified-Since']) && !empty($headers['If-Modified-Since']))
			    {
			        header('Not Modified',true,304);
			        exit;
			    }
			}

			header('Content-Type: '.get_mime_by_extension(basename($path)));
			header('Cache-Control: max-age=3600, must-revalidate');
			header('Last-Modified: '.standard_date('DATE_COOKIE',filemtime($path)));

			echo $data;

			exit;

		}
		else
		{
			show_error('Asset does not exist in repository.',404);
		}
	}

}