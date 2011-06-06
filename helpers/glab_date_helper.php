<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
	
	function date_user ($time, $show_time=true, $return='Never') {
		
		if (!$time) return $return;
		if (!is_numeric($time)) $time = strtotime($time);
		
		$CI =& get_instance();
		$user = $CI->session->userdata('userData');
		
		if (date('Y') === date('Y',$time) && date('n') === date('n',$time) && date('j') === date('j',$time)) $output = 'Today';
		else $output = date('F j, Y',$time);
		
		if ($show_time AND date('H',$time) != 0 AND date('i',$time) != 0) {
			$output.= ' at ';
			
			if (isset($user['prefs']) && $user['prefs']['timeformat'] == 1) $output .= date('H:i T',$time);
			else $output .= date ('g:i a T',$time);
		}
		
		return $output;
	}
	
// End of file.