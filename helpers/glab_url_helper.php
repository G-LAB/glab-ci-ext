<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

function controller_path () {
	$CI =& get_instance();
	$segments = $CI->uri->segment_array();
	
	$controllerSegment = 0;
	foreach ($segments as $id=>$segment)
		if ($segment == $CI->router->fetch_class()) $controllerSegment = $id;
	
	foreach ($segments as $id=>$segment)
		if ($id > $controllerSegment) unset($segments[$id]);
		
	$path = implode('/',$segments).'/';
	
	return $path;
}

function controller_url () { 
	$CI =& get_instance();
	$a_ruri = $CI->uri->segment_array();
	
	// Check If Using Query Strings
	if ($CI->input->get('c')) return site_url();
	
	foreach ($a_ruri as $key=>$ruri)
		if (strtolower($ruri) == strtolower($CI->router->fetch_class())) $ruri_key = $key;
	foreach ($a_ruri as $key=>$ruri)
		if ($key > $ruri_key) unset($a_ruri[$key]);
	return rtrim(site_url(),'/').'/'.implode('/',$a_ruri); 

}

function company_url ($uri) {
	if (preg_match("/glabdev.net/", $_SERVER['SERVER_NAME'])) return site_url($uri);
	else return 'http://glabstudios.com/'.$uri;
}