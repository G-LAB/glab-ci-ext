<?php

class Domain extends CI_Model {
	
	function __construct () {
		parent::__construct();
		$this->load->library('OpenSRS');
	}
	
	function get ($domain) {
		return $this->opensrs->domain()->get($domain);
	}
	
	function accounts ($eid=false,$offset=0) {
		$domains = $this->db->limit(10, $offset)->from('domains');//->order_by("CONCAT(lastName,firstName,companyName)");
		if ($eid) $domains = $domains->where('eid', $eid);
		$domains = $domains->get()->result_array();
		
		foreach ($domains as $row) {
			$domain = $this->opensrs->domain();
			$data[element('eid',$row)][element('domain',$row)] = $domain->get(element('domain',$row));
		}
		
		return $data;
	}
	
	function is_available ($domain) {
		return $this->opensrs->domain()->lookup($domain);
	}
	
}

// End of File