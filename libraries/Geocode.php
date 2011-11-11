<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * G-LAB Geocode Library for Code Igniter v2
 * Written by Ryan Brodkin
 * Copyright 2011
 */

class Geocode
{ 
	public $yahoo;
	public $google;

	function __construct () {
		
		require_once "GoogleGeocode.php";
		$this->google = new GoogleGeocode(false);

		//require_once "YahooGeocode.php";
		//$this->yahoo = new YahooGeocode(false);
	}

	function lookup_postal_code($address)
	{
		$data = $this->google->geocode($address);

		if (isset($data['Placemarks'][0]['PostalCode']) === true)
		{
			return $data['Placemarks'][0]['PostalCode'];
		}
	}
	
}