<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

function controller_name () {
	$CI =& get_instance();
	$CI->load->helper('typography');
	return ucwords(method_clean($CI->router->fetch_class()));
}

function method_name () {
	$CI =& get_instance();
	$CI->load->helper('typography');
	return ucwords(method_clean($CI->router->fetch_method()));
}

function domain_filter ($url) {
	
	if (!$url) return FALSE;
	
	$host = parse_url($url, PHP_URL_HOST);
	$host = array_reverse(explode('.',$host));
	
	if ($host[0] == 'uk') return $host[2].'.'.$host[1].'.'.$host[0]; 
	else return $host[1].'.'.$host[0];
	
}

function greeting () {
	$hour = date("H");
	
	if ($hour < 12) { 
		return "Good Morning"; 
	} elseif ($hour < 17) { 
		return "Good Afternoon"; 
	} else { 
		return "Good Evening"; 
	} 
}

function google_map ($size, $coordinates) {
	
	if (func_num_args() < 1) return FALSE;
	
	$params['size'] = $size;
	//$params['maptype'] = 'terrain';
	$params['sensor'] = 'false';
	
	$params['markers'] = null;
	if (is_string($coordinates)) {	
		$args = func_get_args();
		unset($args[0]);
		$coordinates = $args;
	}
	foreach ($coordinates as $id=>$coordinate) {
		$params['markers'] .= $coordinate.'|';
	}
	
	return "http://maps.google.com/maps/api/staticmap?".http_build_query($params);
	
}