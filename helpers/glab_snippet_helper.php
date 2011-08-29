<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

function entity_link($eid,$name=null,$return=FALSE,$trim=FALSE)
{
	trigger_error('Snippet helper\'s entity_link() has been depreciated in favor of profile_link().',E_USER_DEPRECATED);
	
	$CI =& get_instance();
	
	// Set CLass
	if ($trim = true) $trim = ' class="nowrap rtrim"';
	else $trim = '';
	
	// Get name if not passed
	if ($name == null) $name = $CI->entity->getValue('name',$eid);
	
	// Return HTML
	if (is_numeric($eid) && $name) return '<a href="/backend/index.php/profile/view/'.$eid.'" onclick="updateHUD(\''.$eid.'\')"'.$trim.'>'.$name.'</a>';
	else return $return;
}

function profile_link($profile)
{
	if ($profile == true)
	{
		$CI =& get_instance();
		$profile = $CI->profile->get($profile);
		
		// Return HTML
		if ($profile == true) 
		{
			$html = '<a href="/backend/index.php/profile/view/'.$profile->pid_hex.'" onclick="updateHUD('.$profile->pid_hex.')">';
			$html.= $profile->name->friendly;
			$html.= '</a>';
			
			return $html;
		}
		else 
		{
			return $return_value;
		}
	}
	else
	{
		return "UNKNOWN CLIENT";
	}
}

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

function pagenav ($actionName=null,$actionURI=null) {
	$CI =& get_instance();
	$CI->load->helper('typography');
	
	$controller = $CI->router->fetch_class();
	$method = $CI->router->fetch_method();
	$methods = get_class_methods($controller);
	foreach ($methods as $key=>$value)
		if (	substr($value,0,1) == '_' 
				|| substr($value,0,2) == 'CI' 
				|| $value == 'Controller' 
				|| $value == 'get_instance'
				|| in_array($value, $CI->display->menuBlacklist)
				|| !is_callable(array($CI,$value))
		) unset($methods[$key]);
	$methods = array_values($methods);
	
	// Find Keys
	$thisKey = FALSE;
	foreach ($methods as $key=>$value) if ($value == $method) $thisKey=$key;
	$prevKey = $thisKey-1;
	$nextKey = $thisKey+1;
	
	// Set Values
	if (isset($methods[$prevKey])) {
		$prevURI = site_url(controller_path().$methods[$prevKey]);
		if ($methods[$prevKey] == 'index') $prevName = controller_name().' Main';
		else $prevName= ucwords(method_clean($methods[$prevKey]));
	}
	if (isset($methods[$nextKey])) {
		$nextURI = site_url(controller_path().$methods[$nextKey]);
		if ($methods[$nextKey] == 'index') $nextName = controller_name().' Main';
		else $nextName= ucwords(method_clean($methods[$nextKey]));
	}
	
	// Process Output
	$output = '<div class="pagenav">';
	
	if (isset($prevName)) $output.= '<a href="'.$prevURI.'" class="left">&lt; '.$prevName.'</a>';
	else $output.= '<span></span>';
	
	if ($actionName && $actionURI) $output.= '<a href="'.site_url($actionURI).'">'.$actionName.'</a>';
	else $output.= '<span></span>';
	
	if (isset($nextName)) $output.= '<a href="'.$nextURI.'" class="right">'.$nextName.' &gt;</a>';
	
	$output.= '</div>';
	
	if (count($methods) > 1) return $output;
	else return FALSE;
}

function related ($title=FALSE, $number_of_items=FALSE) {
	
	$CI =& get_instance();
	$CI->load->helper('typography');
	
	$services["analytics"] = array(
								"title" => "Website Analytics",
								"description" => "Knowledge is power... How many unique views do you get?  Where do your website visitors come from?  What's your ROI?\nFind out today!",
								"uri" => "services/analytics"
							);
	$services["hosting"] = array(
								"title" => "Web Hosting",
								"description" => "Why trust us with your website? There’s more to it than just really fast servers.  Get state of the art technology at a price that's right.\nStarting at only $7/mo.",
								"uri" => "products/web_hosting"
							);
	$services["video_production"] = array(
								"title" => "Video Production",
								"description" => "Become the next viral craze, train your employees, or just inform your clients about the products and services that you provide.\nSell yourself in video!",
								"uri" => "services/media_production"
							);
	$services["web_development"] = array(
								"title" => "Web Development",
								"description" => "The Internet moves fast. You need a website that is able to keep up with the times and serve your customer's needs.  Our team is ready to do it.\nUpdate your website now!",
								"uri" => "services/web_development"
							);
	$services["advertising"] = array(
								"title" => "Online Advertising",
								"description" => "It's great to have a website, but you need advertising if people are going to find it.  With the advent of social media, it is easier to target your message than ever before!",
								"uri" => "services/advertising"
							);
	$services["graphic_design"] = array(
								"title" => "Graphic Design",
								"description" => "It doesn't matter whether you need a design for your latest brochure, a new menu layout, or a banner for your online ad... web or print, we do it all.",
								"uri" => "services/media_production/graphic_design"
							);
	$services["photography"] = array(
								"title" => "Photography",
								"description" => "Don't just hire anyone to photograph your business.  Photography needs to be skillfully executed to portray your company the way it deserves.",
								"uri" => "services/media_production/photography"
							);
	$services["marketing_plans"] = array(
								"title" => "Marketing Plans",
								"description" => "Where should you spend your marketing budget and why?  Don't guess.  We'll tell you. Spend your dollars wisely and make your money work for you.",
								"uri" => "services/marketing/marketing_plans"
							);
	
	if (!$title) $title = 'Other services you might enjoy...';
	
	// Get All Passed Arguments
	$active = func_get_args();
	
	// Unset $title Argument
	unset($active[0]);
	
	// Make Current Controller Unavailable
	$controller = $CI->router->fetch_class();
	if (isset($services[$controller])) unset($services[$controller]);
	
	// Check If Minimum Number of Items Is Requested
	if (!is_numeric($number_of_items)) $number_of_items = 3;
	if ($number_of_items > count($services)) $number_of_items = count($services);
	
	// Unset Active Item If Not A String
	foreach ($active as $key=>$value) if (!is_string($value)) unset($active[$key]);
	
	// Fill In Array if Empty or Too Few Items
	if (count($active) < $number_of_items) {
		$keys = array_keys($services);
		shuffle($keys);
		$active = array_merge(array_slice($keys, 0, ($number_of_items - count($active))), $active);
	}
	
	foreach ($active as $realkey=>$key)
		if (!isset($services[$key])) unset($active[$realkey]);
	
	
	echo '<header>
		<h3>'.$title.'</h3>
	</header>
	
	<div class="grid related">';
	foreach ($active as $key) {
		echo '<figure><h4>'.$services[$key]['title'].'</h4><p>'.auto_typography($services[$key]['description']).'</p><a href="'.site_url($services[$key]['uri']).'" class="button">Learn More</a></figure>';
	}
	echo '</div>';
}