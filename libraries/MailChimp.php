<?php

require_once 'MCAPI.class.php';

/**
* Wrapper for the Mail Chimp API Wrapper
*/

class MailChimp {

	private $list = '';
	private $mcapi;
	private $total;

	/**
	* Constructor
	*/
	public function __construct()
	{
		$CI =& get_instance();
		$CI->load->helper('array');
		$CI->load->config('auth', true);

		$key = element('auth_mailchimp_key', $CI->config->item('auth'));
		$this->list = element('auth_mailchimp_list', $CI->config->item('auth'));

		$this->mcapi = new MCAPI($key, true);
	}

	function __call($method, $params)
	{
		$mcapi = $this->mcapi;
		$result = call_user_func_array(array($mcapi,$method), $params);

		// Total Results
		if (isset($result['total']) === true)
		{
			$this->total = $result['total'];
		}
		else
		{
			$this->total = null;
		}

		// Data
		if (isset($result['data']) === true)
		{
			return $result['data'];
		}
		else
		{
			return $result;
		}
	}

	function error()
	{
		return $this->mcapi->errorMessage;
	}

	/**
	 * Get List ID as Set in Config File
	 * @return string MailChimp list ID
	 */
	public function list_id()
	{
		return $this->list;
	}

	/**
	 * Total Results Returned by API
	 * @return int total
	 */
	public function total()
	{
		return $this->total;
	}

}
