<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Plesk Library
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 * @author		Ryan Brodkin
*/

class Plesk
{

	private $xml;
	private $packet;

	private $server;

	public $errors;

	function __construct() {

		$this->xml = new DOMDocument("1.0");

	}

	function init ($host, $username, $password) {

		if (func_num_args() == 3) {
			$this->server['host'] = $host;
			$this->server['username'] = $username;
			$this->server['password'] = $password;
		} else {
			trigger_error('Host, Username, and Password are required.');
		}
	}

	function getCustomer ($client=null) {

		$root = $this->xml->createElement('customer');

			// GET
			$get = $this->xml->createElement('get');
			$root->appendChild($get);

				// FILTER
				$filter = $this->xml->createElement('filter');
				$get->appendChild($filter);

					// FILTER FIELD
					if (is_numeric($client)) $filter_field = $this->xml->createElement('id');
					elseif ($this->is_guid($client)) $filter_field = $this->xml->createElement('guid');
					else $filter_field = $this->xml->createElement('login');
					$filter_field_value = $this->xml->createTextNode($client);
					$filter_field->appendChild($filter_field_value);
					$filter->appendChild($filter_field);

				// DATASET
				$dataset = $this->xml->createElement('dataset');
				$get->appendChild($dataset);

					// GENERAL INFO
					$gen_info = $this->xml->createElement('gen_info');
					$dataset->appendChild($gen_info);

		$result = $this->_request($root);

		$output = array();

		foreach ($result as $id=>$row) {
			$output = (array) $row->data->gen_info;
			$output['id'] = (int) $row->id;
		}

		return $output;


	}

	function getCustomers ($client=null) {

		// INPUT MUST BE ARRAY
		if (!is_array($client) && $client != null) $client = array($client);

		// CREATE ROOT ELEMENT
		$root = $this->xml->createElement('customer');

			// ITERATE INPUT
			foreach ($client as $value) {

				// GET
				$get = $this->xml->createElement('get');
				$root->appendChild($get);

					// FILTER
					$filter = $this->xml->createElement('filter');
					$get->appendChild($filter);

						// FILTER FIELDS
						if ($client != null) {
							if ($this->is_guid($value)) $filter_field = $this->xml->createElement('guid');
							else $filter_field = $this->xml->createElement('login');
							$filter_field_value = $this->xml->createTextNode($value);
							$filter_field->appendChild($filter_field_value);
							$filter->appendChild($filter_field);
						}

					// DATASET
					$dataset = $this->xml->createElement('dataset');
					$get->appendChild($dataset);

						// GENERAL INFO
						$gen_info = $this->xml->createElement('gen_info');
						$dataset->appendChild($gen_info);
			} // FOREACH

		$result = $this->_request($root);

		$output = array();

		foreach ($result as $row) {
			$guid = (string) $row->data->gen_info->guid;

			$output[$guid] = (array) $row->data->gen_info;
			$output[$guid]['id'] = (int) $row->id;
		}

		return $output;

	}

	function getServer () {

		$datasets = array('key','gen_info','components','stat','interfaces','services_state');

		$root = $this->xml->createElement('server');

			// GET
			$get = $this->xml->createElement('get');
			$root->appendChild($get);

				// DATA SETS
				foreach ($datasets as $set) {
					$set_element = $this->xml->createElement($set);
					$get->appendChild($set_element);
				}


		$result = $this->_request($root);
		$result = $result[0];

		$output = array();
		foreach ($datasets as $set) {

			if ($set == 'key') {
				$output['key'] = $this->key_value_pairs($result->key->property);
			} elseif ($set == 'gen_info') {
				$output['gen_info'] = (array) $result->gen_info;
				unset($output['gen_info']['vps-optimized-status']);

			} elseif ($set == 'components') {
				$output['components'] = $this->key_value_pairs($result->components->component,'name','version');

			} elseif ($set == 'stat') {
				foreach ((array) $result->stat as $key=>$object) $output['stat'][$key] = (array) $object;
				$output['stat']['diskspace'] = (array) $result->stat->diskspace->device;


			} elseif ($set == 'services_state') {
				//$output['services_state'] = (array) $result->services_state;
				foreach ((array) $result->services_state->xpath('//srv') as $object) $output['services_state'][] = (array) $object;

			} else $output[$set] = (array) $result->$set;

		}

		return $output;

	}

	function getSubscription($domain) {

		$datasets = array('gen_info','hosting','limits','stat','prefs','disk_usage','performance','subscriptions','permissions');

		$root = $this->xml->createElement('webspace');

			// GET
			$get = $this->xml->createElement('get');
			$root->appendChild($get);

				// FILTER
				$filter = $this->xml->createElement('filter');
				$get->appendChild($filter);

					// FILTER FIELD
					if (is_numeric($domain)) $filter_field = $this->xml->createElement('id');
					elseif ($this->is_guid($domain)) $filter_field = $this->xml->createElement('guid');
					else $filter_field = $this->xml->createElement('name');
					$filter_field_value = $this->xml->createTextNode($domain);
					$filter_field->appendChild($filter_field_value);
					$filter->appendChild($filter_field);

				// DATASET
				$dataset = $this->xml->createElement('dataset');
				$get->appendChild($dataset);

					// DATA SETS
					foreach ($datasets as $set) {
						$set_element = $this->xml->createElement($set);
						$dataset->appendChild($set_element);
					}

		$result = $this->_request($root);

		$output = array();

		foreach ((array) $result[0]->data as $dataset=>$object) {

			if ($dataset == 'hosting') {
				$output['hosting'] = $this->key_value_pairs($object->vrt_hst->property);
				$output['hosting']['ip_address'] = (string) $object->vrt_hst->ip_address;
			} elseif ($dataset == 'limits') {
				$output['limits'] = $this->key_value_pairs($object->limit);
				$output['limits']['overuse'] = (string) $object->overuse;
			} elseif ($dataset == 'permissions') {
				$output['permissions'] = $this->key_value_pairs($object->permission);
			} else $output["$dataset"] = (array) $object;
		}

		return $output;

	}

	function getSubscriptions($client=null) {

		// INPUT MUST BE ARRAY
		if (!is_array($client)) $client = array($client);

		// CREATE ROOT ELEMENT
		$root = $this->xml->createElement('webspace');

			foreach ($client as $value) {

				// GET
				$get = $this->xml->createElement('get');
				$root->appendChild($get);

					// FILTER
					$filter = $this->xml->createElement('filter');
					$get->appendChild($filter);

						// FILTER FIELD
						if ($client != null) {
							if ($this->is_guid($value)) $filter_field = $this->xml->createElement('owner-guid');
							else $filter_field = $this->xml->createElement('owner-login');
							$filter_field_value = $this->xml->createTextNode($value);
							$filter_field->appendChild($filter_field_value);
							$filter->appendChild($filter_field);
						}

					// DATASET
					$dataset = $this->xml->createElement('dataset');
					$get->appendChild($dataset);

						// HOSTING
						$hosting = $this->xml->createElement('hosting');
						$hosting_value = $this->xml->createTextNode('');
						$hosting->appendChild($hosting_value);
						$dataset->appendChild($hosting);

			} // FOREACH

		$result = $this->_request($root);

		$output = array();

		foreach ($result as $id=>$row) {
			$output[$id] = (array) $row->data->gen_info;
			$output[$id]['features'] = $this->key_value_pairs($row->data->hosting->vrt_hst->property);
		}

		return $output;

	}

	function updateCustomerPassword ($customer, $password) {

		$root = $this->xml->createElement('customer');

			// GET
			$set = $this->xml->createElement('set');
			$root->appendChild($set);

				// FILTER
				$filter = $this->xml->createElement('filter');
				$set->appendChild($filter);

					// FILTER FIELD
					if (is_numeric($customer)) $filter_field = $this->xml->createElement('id');
					elseif ($this->is_guid($customer)) $filter_field = $this->xml->createElement('guid');
					else $filter_field = $this->xml->createElement('login');
					$filter_field_value = $this->xml->createTextNode($customer);
					$filter_field->appendChild($filter_field_value);
					$filter->appendChild($filter_field);

				// VALUES
				$values = $this->xml->createElement('values');
				$set->appendChild($values);

					// HOSTING
					$gen_info = $this->xml->createElement('gen_info');
					$values->appendChild($gen_info);

						// VALUE
						$passwd = $this->xml->createElement('passwd');
						$passwd_value = $this->xml->createTextNode($password);
						$passwd->appendChild($passwd_value);
						$gen_info->appendChild($passwd);

		$result = $this->_request($root);

		if (!isset($result->system->errcode)) return true;
		else return false;
	}

	function updateSubscriptionPassword ($domain, $ip_address, $password) {

		$root = $this->xml->createElement('webspace');

			// GET
			$set = $this->xml->createElement('set');
			$root->appendChild($set);

				// FILTER
				$filter = $this->xml->createElement('filter');
				$set->appendChild($filter);

					// FILTER FIELD
					if (is_numeric($domain)) $filter_field = $this->xml->createElement('id');
					elseif ($this->is_guid($domain)) $filter_field = $this->xml->createElement('guid');
					else $filter_field = $this->xml->createElement('name');
					$filter_field_value = $this->xml->createTextNode($domain);
					$filter_field->appendChild($filter_field_value);
					$filter->appendChild($filter_field);

				// VALUES
				$values = $this->xml->createElement('values');
				$set->appendChild($values);

					// HOSTING
					$hosting = $this->xml->createElement('hosting');
					$values->appendChild($hosting);

						// VIRTUAL HOST
						$vrt_hst = $this->xml->createElement('vrt_hst');
						$hosting->appendChild($vrt_hst);

							// PROPERTY
							$property = $this->xml->createElement('property');
							$vrt_hst->appendChild($property);

								// NAME
								$name = $this->xml->createElement('name');
								$name_value = $this->xml->createTextNode('ftp_password');
								$name->appendChild($name_value);
								$property->appendChild($name);

								// VALUE
								$value = $this->xml->createElement('value');
								$value_value = $this->xml->createTextNode($password);
								$value->appendChild($value_value);
								$property->appendChild($value);

							// IP ADDRESS
							$ip = $this->xml->createElement('ip_address');
							$ip_value = $this->xml->createTextNode($ip_address);
							$ip->appendChild($ip_value);
							$vrt_hst->appendChild($ip);

		$result = $this->_request($root);

		if (!isset($result->system->errcode)) return true;
		else return false;

	}

	function controlService ($service,$action='restart') {

		if (!is_array($service)) $service = array($service);

		$root = $this->xml->createElement('server');

			foreach ($service as $srv_id) {

			// SRV MAN
			$srv_man = $this->xml->createElement('srv_man');
			$root->appendChild($srv_man);

				// ID
				$id = $this->xml->createElement('id');
				$id_value = $this->xml->createTextNode($srv_id);
				$id->appendChild($id_value);
				$srv_man->appendChild($id);

				// OPERATION
				$operation = $this->xml->createElement('operation');
				$operation_value = $this->xml->createTextNode($action);
				$operation->appendChild($operation_value);
				$srv_man->appendChild($operation);

			}

		$result = $this->_request($root);

		return TRUE;

	}

	private function key_value_pairs ($obj,$key='name',$value='value') {

		if (!is_object($obj)) return array();

		$output = array();

		foreach ($obj as $row) $output[(string) $row->$key] = (string) $row->$value;

		return $output;
	}

	private function is_guid ($str) {

		return preg_match("/^(\{)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)\})$/i", $str);

	}

	private function array_first ($array) {

		return array_shift(array_values($array));

	}

	private function _request($obj) {

		// Generate Base Tree
		$packet = $this->xml->createElement("packet");
		$packet_version = $this->xml->createAttribute('version');
		$packet_version_text = $this->xml->createTextNode('1.6.3.1');
		$packet_version->appendChild($packet_version_text);
		$packet->appendChild($packet_version);

		// Add Method Data to Tree
		$packet->appendChild($obj);

		// Add Everything to DOM
		$this->xml->appendChild($packet);

		// Generate XML
		$packet = $this->xml->saveXML();
		/*$packet='<?xml version="1.0"?><packet version="1.4.2.0"><domain><get><filter><client_id>1</client_id></filter><dataset><hosting></hosting></dataset></get></domain></packet>';*/
		// Store Request for Debugging
		$this->request_xml = $packet;
		$this->request = simplexml_load_string($packet);

		$port = 8443;
		$path = 'enterprise/control/agent.php';
		$url = 'https://' . $this->server['host'] . ':' . $port . '/' . $path;

		$headers = array(
			'HTTP_AUTH_LOGIN: '.$this->server['username'],
			'HTTP_AUTH_PASSWD: '.$this->server['password'],
			'Content-Type: text/xml'
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $packet);
		$result = curl_exec($ch);

		$this->response = simplexml_load_string($result);
		$this->response_xml = $result;

		curl_close($ch);

		// Extract Results Tree
		$results = $this->response->xpath('//result');

		// Clear DOM
		$this->__construct();

		if (isset( $this->response->system )) {
			$this->errors[] = $this->response->system;
			return FALSE;
		} else {
			return $results;
		}

	}

}

// End of file.