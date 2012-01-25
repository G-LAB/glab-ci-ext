<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * G-LAB Plesk Library for Code Igniter v2
 * Written by Ryan Brodkin
 * Copyright 2011
 */

class OpenSRS {

	private $config = array();

	public $domain;
	public $ssl;

	function __construct() {

		// Load Codeigniter, If Available
		if (function_exists('get_instance')) $CI = &get_instance();

		// Load Authentication Values From Config, If Present
		if (	isset($CI)
				AND $CI->config->item('auth_opensrs_user')
				AND $CI->config->item('auth_opensrs_key')
		) {
			$this->config['user'] = $CI->config->item('auth_opensrs_user');
			$this->config['key'] = $CI->config->item('auth_opensrs_key');
		}

		// Default to Main API Server
		$this->config['host'] = 'rr-n1-tor.opensrs.net';

	}

	function init ($username, $key) {

		if (func_num_args() == 2) {
			$this->config['username'] = $username;
			$this->config['key'] = $key;
			return true;
		} else {
			trigger_error('Username, and Key are required.');
			return false;
		}
	}

	function account () {
		return new OpenSRS_Account($this->config);
	}

	function domain () {
		return new OpenSRS_Domain($this->config);
	}

	function ssl () {
		return new OpenSRS_SSL($this->config);
	}

	function testMode ($bool=true) {
		if ($bool == true) $this->config['host'] = 'horizon.opensrs.net';
	}

}

class OpenSRS_Common {

	protected $xml;
	private $config;
	protected $message;

	function __construct($config) {
		$this->config = $config;

		$imp = new DOMImplementation;
		$dtd = $imp->createDocumentType('OPS_envelope', '', 'ops.dtd');
		$dom = $imp->createDocument("", "", $dtd);
		$dom->encoding = 'UTF-8';
		$dom->standalone = false;

		$this->xml = $dom;
	}

	public function getMessage () {
		return (string) $this->message;
	}

	protected function _request($action, $object, $domain=false, $attrs=array()) {

		// Trigger error if username or key not found
		if (!isset($this->config['user'], $this->config['key'])) {
			trigger_error('OpenSRS key and/or username not configured.');
			exit;
		}

		// Generate Base Tree
		$packet = $this->xml->createElement("OPS_envelope");

		$header = $this->xml->createElement("header");
		$packet->appendChild($header);

			$version = $this->xml->createElement("version");
			$version_value  = $this->xml->createTextNode("0.9");
			$version->appendChild($version_value);
			$header->appendChild($version);

		$body = $this->xml->createElement("body");
		$packet->appendChild($body);

			$data_block = $this->xml->createElement("data_block");
			$body->appendChild($data_block);

				$data_block_a['protocol'] = 'XCP';
				$data_block_a['action'] = $action;
				$data_block_a['object'] = $object;
				if ($domain) $data_block_a['domain'] = $domain;

				$dt_assoc = $this->genDtAssoc($data_block_a);
				$data_block->appendChild($dt_assoc);

				$item = $this->xml->createElement("item");
				$item_key = $this->xml->createAttribute('key');
				$item_key_value = $this->xml->createTextNode('attributes');
				$item_key->appendChild($item_key_value);
				$item->appendChild($item_key);
				$dt_assoc->appendChild($item);

				$item->appendChild($this->genDtAssoc($attrs));

		// Add Everything to DOM
		$this->xml->appendChild($packet);

		// Generate XML
		$packet = $this->xml->saveXML();

		// Store Request for Debugging
		$this->request_xml = $packet;
		$this->request = $this->processOutput(simplexml_load_string($packet));

		$port = 55443;
		$url = 'https://' . $this->config['host'] . ':' . $port . '/';

		$headers = array(
			'Content-Type: text/xml',
			'X-Username: '.$this->config['user'],
			'X-Signature: '.$this->signRequest($packet),
			'Content-Length: '.strlen($packet)
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $packet);

		$result = curl_exec($ch);
		curl_close($ch);

		// Verify Output
		if (strlen($result) == 0) {
			trigger_error('OpenSRS API returned empty result.');
			return false;
		} else {
			$this->response = $this->processOutput(simplexml_load_string($result));
			$this->response_xml = $result;
		}

		$data = $this->response;
		$data = $data['body']['data_block'];

		// Check For Messages
		if (isset($data['response_text'])) $this->message = $data['response_text'];

		// Return Results
		if (!isset($data['is_success']) OR $data['is_success'] == false) {
			return FALSE;
		} else {
			return $data['attributes'];
		}
	}

	protected function genDtAssoc ($data, $dt_array=false) {

		if ($dt_array == true) $dt_assoc = $this->xml->createElement("dt_array");
		else $dt_assoc = $this->xml->createElement("dt_assoc");

		foreach ($data as $key=>$value) {
			$item = $this->xml->createElement("item");
			$item_key = $this->xml->createAttribute('key');
			$item_key_value = $this->xml->createTextNode((string) $key);

			if (is_object($value)) $item_value = $value;
			elseif (is_array($value)) $item_value = $this->genDtAssoc($value, true);
			else $item_value = $this->xml->createTextNode((string) $value);

			$item_key->appendChild($item_key_value);
			$item->appendChild($item_key);
			$item->appendChild($item_value);
			$dt_assoc->appendChild($item);
		}

		return $dt_assoc;

	}

	private function processOutput ($obj) {

		if (!is_object($obj)) return FALSE;

		$output = array();
		foreach ($obj as $key=>$node) {
			$node_attr = $node->attributes();
			if (isset($node_attr['key'])) $node_key = (string) $node_attr['key'];
			$node_output = $this->processOutput($node);

			if ($key == 'dt_assoc') return $node_output;
			elseif ($key == 'dt_array') return $node_output;

			// Assign Outout to Correct Key
			if (isset($node_attr['key'])) {
				if (count($node_output) == 0) $output[$node_key] = (string) $node;
				else $output[$node_key] = $node_output;
			} elseif (is_string($key)) {
				$output[$key] = $node_output;
			}
		}

		return $output;
	}

	private function signRequest ($str) {

		return md5(md5($str.$this->config['key']).$this->config['key']);
	}

}

class OpenSRS_Domain extends OpenSRS_Common {

	function belongs_to_rsp ($domain) {

		$request['domain'] = $domain;

		return $this->_request('belongs_to_rsp', 'domain', null, $request);
	}

	function get ($domain) {

		$request['type'] = 'all_info';
		$request['limit'] = 10;

		return $this->_request('get', 'domain', $domain, $request);
	}

	function get_availability ($domain) {

		$request['domain'] = $domain;

		return $this->_request('lookup', 'domain', false, $request);
	}

	function get_contacts ($domains) {

		if (!is_array($domains)) $domains = array($domains);

		$request['domain_list'] = $domains;

		return $this->_request('get_domains_contacts', 'domain', null, $request);

	}

	function get_deleted ($domain) {

		$request['domain'] = $domain;

		return $this->_request('get_deleted_domains', 'domain', null, $request);
	}

	function get_deleted_by_date ($del_from, $del_to, $limit=50, $page=0) {

		if (!is_numeric($del_from)) $del_from = strtotime($del_from);
		if (!is_numeric($del_to)) $del_to = strtotime($del_to);

		$request['del_from'] = date('Y-m-d', $del_from);
		$request['del_to'] = date('Y-m-d', $del_to);

		$request['limit'] = $limit;
		$request['page'] = $page;

		return $this->_request('get_deleted_domains', 'domain', null, $request);
	}

	function get_expiring ($exp_from, $exp_to, $limit=50, $page=0) {

		if (!is_numeric($exp_from)) $exp_from = strtotime($exp_from);
		if (!is_numeric($exp_to)) $exp_to = strtotime($exp_to);

		$request['exp_from'] = date('Y-m-d', $exp_from);
		$request['exp_to'] = date('Y-m-d', $exp_to);

		$request['limit'] = $limit;
		$request['page'] = $page;

		return $this->_request('get_domains_by_expiredate', 'domain', null, $request);

	}

	function get_notes ($domain, $limit=50, $page=0) {

		$request['type'] = 'domain';
		$request['domain'] = $domain;
		$request['limit'] = $limit;
		$request['page'] = $page;

		return $this->_request('get_notes', 'domain', null, $request);
	}

	function get_order ($order_id) {

		$request['order_id'] = $order_id;

		return $this->_request('get_order_info', 'domain', null, $request);
	}

	function get_orders ($domain, $limit=50, $page=0) {

		$request['domain'] = $domain;
		$request['limit'] = $limit;
		$request['page'] = $page;

		return $this->_request('get_orders_by_domain', 'domain', null, $request);
	}

	function get_order_notes ($domain, $order_id, $limit=50, $page=0) {

		$request['type'] = 'order';
		$request['domain'] = $domain;
		$request['order_id'] = $order_id;
		$request['limit'] = $limit;
		$request['page'] = $page;

		return $this->_request('get_notes', 'domain', null, $request);
	}

	function get_price ($domain, $period=1, $renewal=false) {

		$request['domain'] = $domain;
		$request['period'] = $period;
		if ($renewal) $request['reg_type'] = 'renewal';
		else $request['reg_type'] = 'new';

		$result = $this->_request('get_price', 'domain', null, $request);

		if (isset($result['price'])) return $result['price'];
		else return false;
	}

	function get_suggestions ($query, $tlds=array('.com', '.net', '.org')) {

		$request['searchstring'] = $query;
		$request['tlds'] = $this->genDtAssoc($tlds, true);

		$result = $this->_request('name_suggest', 'domain', null, $request);

		return $result;
	}

	function get_transfer_notes ($domain, $transfer_id, $limit=50, $page=0) {

		$request['type'] = 'transfer';
		$request['domain'] = $domain;
		$request['transfer_id'] = $transfer_id;
		$request['limit'] = $limit;
		$request['page'] = $page;

		return $this->_request('get_notes', 'domain', null, $request);
	}

	function new_order ($domain, $period, $type, $reg_username, $reg_password, $contact_owner, $contact_tech=false, $contact_admin=false, $contact_billing=false, $nameservers=false, $auto_renew=false, $lock_reg=true) {

		// Auto-Renew
		if ($auto_renew) $request['auto_renew'] = 1;
		else $request['auto_renew'] = 0;

		// Contact Set
		$request['contact_set'] = $this->gen_contact_set($contact_owner, $contact_admin, $contact_billing, $contact_tech);

		// Nameservers
		if (is_array($nameservers)) {
			$request['custom_nameservers'] = 1;
			$request['nameserver_list'] = $this->gen_nameserver_pair($nameservers);
		} else {
			$request['custom_nameservers'] = 0;
		}

		// Tech Contact
		if ($contact_tech) $request['custom_tech_contact'] = 1;
		else $request['custom_tech_contact'] = 0;

		// Domain Name
		$request['domain'] = "$domain";

		// Lock Domain to Prevent Transfer
		if ($lock_reg) $request['f_lock_domain'] = 1;
		else $request['f_lock_domain'] = 0;

		// Confirmation Email Address (.AU, .BE, .DE, .EU or .IT transfers only)
		if (isset($contact_owner['email'])) $request['owner_confirm_address'] = $contact_owner['email'];

		// Registration Period
		$request['period'] = (int) $period;

		// Username ad Password
		$request['reg_username'] = $reg_username;
		$request['reg_password'] = $reg_password;

		// Registration Type
		if (in_array($type, array('new', 'premium', 'transfer', 'sunrise'))) {
			$request['reg_type'] = $type;
		} else {
			trigger_error("Registration type must be equal to 'new,' 'premium,' transfer,' or 'sunrise.'");
		}

		// Order Handling (process or save)
		$request['handle'] = 'process';

		return $this->_request('sw_register', 'domain', null, $request);
	}

	private function gen_contact_set($owner, $admin=false, $billing=false, $tech=false) {

		// Owner
		$contacts['owner'] = $this->genDtAssoc($owner);

		// Admin
		if ($admin) $contacts['admin'] = $this->genDtAssoc($admin);
		else $contacts['admin'] = $this->genDtAssoc($owner);

		// Billing
		if ($billing) $contacts['billing'] = $this->genDtAssoc($billing);
		else $contacts['billing'] = $this->genDtAssoc($owner);

		// Tech
		if ($tech) $contacts['tech'] = $this->genDtAssoc($tech);
		else $contacts['tech'] = $this->genDtAssoc($owner);

		return $this->genDtAssoc($contacts);

	}

	private function gen_nameserver_pair ($data) {

		$data = array_values($data);

		foreach ($data as $id=>$ns) {

			$server['sortorder'] = $id+1;
			$server['name'] = $ns;

			$servers[] = $this->genDtAssoc($server);
		}

		return $this->genDtAssoc($servers, true);

	}

}

class OpenSRS_SSL { }

// End of file.