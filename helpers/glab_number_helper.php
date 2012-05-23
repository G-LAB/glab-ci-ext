<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

function acctnum_format($str)
{
	// Strip Non-Digits
	$str = preg_replace("/[^0-9]/", "", $str);

	if (empty($str) === true)
	{
		return '';
	}

	// Pad Older PIDs to 16 Digits
	$str = str_pad($str, 16, '00000000', STR_PAD_LEFT);

	return preg_replace("/([\d]{4})([\d]{4})([\d]{5})([\d]+)/", "$1 $2 $3 $4", $str);

}

function tikid_format($tikid)
{
	if (empty($tikid)) return '';
	return strtoupper(preg_replace("/([0-9a-zA-Z]{4})([0-9a-zA-Z]{5})([0-9a-zA-Z]+)/", "$1-$2-$3", $tikid));
}

// End of File