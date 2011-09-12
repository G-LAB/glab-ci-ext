<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * G-LAB Event Model for Code Igniter v2
 * Written by Ryan Brodkin
 * Copyright 2011
 */

class Event extends CI_Model
{ 
	public function __construct()
	{
		if (isset($this->db->conn_id) != true)
		{
			trigger_error('Cannot operate Event model without database.');
		}	
	}
	
	// Log New Event
	public function log($event_type,$pid=false,$data=false)
	{
		$ip_address = $this->input->ip_address();
		
		$q = $this->db	->set('event_type', $event_type)
						->set('ip_address', 'INET_ATON(\''.$ip_address.'\')',false);
		
		if ($pid !== false)
		{
			$q->set('profile', $pid);
		}
		
		if ($data !== false)
		{
			$q->set('data',serialize($data));
		}
					
		if ($q->insert('event_log'))
		{
			return $this->db->insert_id();
		}
		else
		{
			return false;
		}
	}
	
	// Get Logfile
	public function get($filter=false,$limit=30,$offset=0)
	{
		$this->load->helper(array('array'));
		$this->load->language('event');
		
		$data = array();
		
		$result = $this->db	
					->select('*')
					->select('inet_ntoa(ip_address) as ip_address',false)
					->order_by('timestamp','desc')
					->limit($limit,$offset)
					->get('event_log')
					->result_array();
		
		foreach ($result as $row) {
			$template = $this->lang->line('event_'.element('event_type',$row));
			$data[] = array_merge($row,array('event_template'=>$template));
		}
		
		return $data;
	}
	
	// Parse Data Into Language File Entry
	private function parse_lang($event_row)
	{
		// Accepts entire row from event log as argument.
	}
	
}
// End of File