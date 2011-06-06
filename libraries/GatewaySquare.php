<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * G-LAB Square Gateway for Code Igniter v2
 * Written by Ryan Brodkin
 * Copyright 2011
 */


/*
	TODO:
	
	1. Run preg_replace on dollar amounts to strip non-numeric characters instead
	   of using ltrim.
	2. Adjust library to extend a base library that will make this more consistent.
	3. Add Payment Entries
		After new payments are added to square db, need to add a payment entry 
		for the invoice using a method in the billingman model.  Method needs to 
		accept the pgid (gateway id) from the db as well as the total payment and
		discount fees.  Model will need to handle adding entries to ledger.

*/

require_once 'GatewayBase.php';

class GatewaySquare extends GatewayBase {
	
	function _cron () {
		$this->CI->load->helper('inflector');
		
		$params['email'] = 'accounts@glabstudios.com';
		$params['password'] = '3hoEnhyefmk3nd';
		
		/*
			SETUP CURL
		*/
		// INIT CURL
		$ch = curl_init();
		// IMITATE CLASSIC BROWSER'S BEHAVIOUR : HANDLE COOKIES
		curl_setopt ($ch, CURLOPT_COOKIEJAR, tmpfile());
		# Setting CURLOPT_RETURNTRANSFER variable to 1 will force cURL
		# not to print out the results of its query.
		# Instead, it will return the results as a string return value
		# from curl_exec() instead of the usual true/false.
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		
		/*
			FETCH LOGIN PAGE W/ NONCE
		*/
		// SET FILE TO DOWNLOAD
		curl_setopt($ch, CURLOPT_URL, 'https://squareup.com/login');
		// EXECUTE 2nd REQUEST (FILE DOWNLOAD)
		$html = curl_exec ($ch);
		
		$dom = new DOMDocument();
		$dom->loadHTML($html);
		
		$xpath = new DOMXPath($dom);
		
		$tags = $xpath->query('//input[@name="authenticity_token"]');
		foreach ($tags->item(0)->attributes as $tag) if ($tag->name == 'value') $params['authenticity_token'] = $tag->value;
		
		if (isset($params['authenticity_token'])) {
			/*
				SEND AUTH
			*/
			// SET URL FOR THE POST FORM LOGIN
			curl_setopt($ch, CURLOPT_URL, 'https://squareup.com/login');
			// ENABLE HTTP POST
			curl_setopt ($ch, CURLOPT_POST, 1);
			// SET POST PARAMETERS : FORM VALUES FOR EACH FIELD
			curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($params));
			// EXECUTE 1st REQUEST (FORM LOGIN)
			$auth = curl_exec ($ch);
			
			if ($auth) {
				/*
					DOWNLOAD CSV
				*/
				// DISABLE HTTP POST
				curl_setopt ($ch, CURLOPT_POST, 0);
				// SET FILE TO DOWNLOAD
				curl_setopt($ch, CURLOPT_URL, 'https://squareup.com/payments.csv');
				// EXECUTE 2nd REQUEST (FILE DOWNLOAD)
				$csv = curl_exec ($ch);
				
				// CLOSE CURL
				curl_close ($ch);
				
				if ($csv) {
					/*
						PROCESS CSV DATA
					*/
					// PROCESS LINE BREAKS
					// Can't Use Explode Due to Potential Line Breaks in CSV Content
					$data = str_getcsv($csv, "\n");
					
					// ASSIGN NUMERICALLY KEYED ARRAYS TO ROWS
					foreach($data as &$row) $row = str_getcsv($row, ",");
					
					// KEYS IN FIRST ROW
					$keys = $data[0];
					
					// PROCESS KEYS TO REMOVE CAPS AND SPACES
					// Function underscore() provided by CI's Inflector helper
					foreach($keys as &$key) $key = underscore($key);
					
					// REMOVE HEADERS FROM DATA
					unset($data[0]);
					
					// MAKE NUMERIC KEYS ASSOCIATIVE
					foreach($data as &$row) $row = array_combine($keys, $row);
					
					if (is_array($data) and count($data) > 0) {
						// WE HAVE LIFTOFF!!!
						
						// GET BIGGEST PRIMARY KEY
						$key_row = $this->CI->db
							->select_max('transid','transid')
							->get($this->table_name)
							->row_array();
						$key = $key_row['transid'];
						
						$payments = array();
						$refunds = array();
						foreach ($data as &$row) if ($row['payment_id'] > $key) {
							
							/*
								PROCESS PAYMENTS
							*/
							if (strtolower($row['transaction_type']) == 'payment') {
								
								$payment['transid'] = $row['payment_id'];
								$payment['ivid'] = $row['description'];
								$payment['pgid'] = $this->gateway_profile['pgid'];
								$payment['discount'] = preg_replace ('/[^\d\.]/', '', $row['fee']);
								$payment['amount'] = preg_replace ('/[^\d\.]/', '', $row['total']);
								$payment['timestamp'] = $row['date'].' '.$row['time'];
								if (strtolower($row['payment_type']) == 'cash') $payment['type'] = 'cash';
								else $payment['type'] = 'card';
								
								$payments[] = $payment;
								unset($payment);
								
							} elseif (strtolower($row['transaction_type']) == 'refund') {
								$refunds[] = $row;
							} else {
								echo 'UNKNOWN PAYMENT TYPE';
							}
							
						}
						
						// BATCH PROCESS SORTED DATA
						// Payments
						if (count($payments) > 0) $success_payments = $this->CI->db->insert_batch($this->table_name, $payments);
						if (isset($success_payments) AND $success_payments == true) echo 'Inserted '.count($payments).' payments into local Square database.'."\n";
						else echo 'No new payments found.'."\n";
						
						// Refunds
						var_dump($refunds);
					}
				}
			}
		}
	}
	
	function get_batch_errors () {
		return $this->CI->db
			->select('g.*')
			->where('orid IS NULL', false, false)
			->where('pgid', $this->gateway_profile['pgid'])
			->join('billing_invoices i', 'g.ivid = i.ivid', 'left')
			->get($this->table_name.' g')
			->result_array();
	}
}
	
// End of file.