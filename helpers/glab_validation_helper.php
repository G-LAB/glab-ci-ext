<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

function is_account_number($str)
{//1262-21760-0
	if (preg_match('/[\d]{4}-[\d]{5}-[\d]+/', $str))
	{echo 'yup!';
		return true;
	}
	else
	{
		return false;
	}
}

// End of File