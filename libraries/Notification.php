<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * G-LAB Document Management Library for Code Igniter v2
 * Written by Ryan Brodkin
 * Copyright 2009
 */

class Notification
{ 
	function email ($tmpl, $data, $recipients, $brand=false, $reply_to=false) {
		$CI =& get_instance();
		$CI->load->library('email');
		$CI->load->library('parser');
		$CI->load->model('profile');
		$CI->load->helper('typography');
		$CI->load->helper('glib_validation');
		
		$config['mailtype'] = 'html';
		
		$CI->email->initialize($config);
		
		// Defalt Brand
		if ($brand == false)
		{
			$brand = 'glab';
		}
		
		// Parse Recipients
		if (is_string($recipients) === true)
		{
			$recipients = explode(',', $recip, 20);
		}
		
		foreach ($recipients as $recip)
		{
			$profile = $CI->profile->get($recip);
			
			// Lookup Recipient
			if ($profile->exists() === true) {
				$email['entity']['name'] = $profile->name->friendly;
				
				if (is_email($recip))
				{
					$email['entity']['email'] = $recip;
				}
				elseif ($profile->email->primary() !== false)
				{
					$email['entity']['email'] = $profile->email->primary();
				}
				else
				{
					$email['entity']['email'] = false;
				}
			} 
			// Otherwise Fill Defaults
			elseif (is_email($recip)) 
			{
				$email['entity']['name'] = "Hello";
				$email['entity']['email'] = $recip;
			} 
			else
			{
				$email['entity']['email'] = false;
			}
			
			$body_text = $CI->parser->parse('_emails/'.$tmpl, $data, TRUE);
			$body_html = $CI->load->view('_emails/msg_'.$brand, array_merge(array('body'=>$body_text, 'from'=>'G LAB Studios'),$email), TRUE);
			
			if (file_exists( APPPATH.'views/_emails/'.$tmpl.'_subject.php' )) $subject 	= $CI->parser->parse('_emails/'.$tmpl.'_subject', $data, TRUE);
			else $subject = 'Important Message from G LAB';
			
			$CI->email->from('noreply@glabstudios.com', 'G LAB');
			$CI->email->to($email['entity']['email']); 
			
			if (is_string($reply_to) === true AND is_email($reply_to) === true)
			{
				$CI->email->reply_to($reply_to);
			}
			
			$CI->email->subject($subject);
			
			$CI->email->message($body_html);	
			$CI->email->set_alt_message($body_text);
			
			if (is_string($email['entity']['email']) === true)
			{
				$success = $CI->email->send();
			}
		}
		
		$CI->email->clear();
		
		if (isset($success) AND $success === true) 
		{
			return $email['entity']['email'];
		}
		else 
		{
			return false;
		}
	}
	
	function admin ($tmpl, $data) {
		$CI =& get_instance();
		$CI->db->select('email, emailSMS');
		$admins = $CI->db->get('entities_admin');
		
		foreach ($admins->result_array() as $admin) {
			if ($admin['email'] != null) $list_email[] 	= $admin['email'];
			if ($admin['emailSMS'] != null) $list_sms[] 	= $admin['emailSMS'];
		}

		if (is_array($list_email)) foreach ($list_email as $email) $this->email($tmpl, $data, $email);
		if (is_array($list_sms)) foreach ($list_sms as $sms) $this->email($tmpl.'_sms', $data, $sms);
	}
}