<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

function acctnum_format($acctnum)
{
	if (empty($acctnum)) return '';
	
	$acctnum = preg_replace("/[^0-9]/", "", $acctnum);
	
	return preg_replace("/([0-9a-zA-Z]{4})([0-9a-zA-Z]{5})([0-9a-zA-Z]+)/", "$1-$2-$3", $acctnum);

}

function tikid_format($tikid)
{
	if (empty($tikid)) return '';
	return strtoupper(preg_replace("/([0-9a-zA-Z]{4})([0-9a-zA-Z]{5})([0-9a-zA-Z]+)/", "$1-$2-$3", $tikid));
}

// End of File