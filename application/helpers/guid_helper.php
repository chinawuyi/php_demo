<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * guid
 *
 * @access	public
 * @return	string
 */
if ( ! function_exists('guid'))
{
	function guid()
	{
		$charid = strtoupper(md5(uniqid(mt_rand(), true)));
		$hyphen = chr(45);// "-"
		$uuid = // "{"
		substr($charid, 0, 8).$hyphen
		.substr($charid, 8, 4).$hyphen
		.substr($charid,12, 4).$hyphen
		.substr($charid,16, 4).$hyphen
		.substr($charid,20,12)
		;// "}"
		return $uuid;
	}
}


/* End of file guid_helper.php */
/* Location: ./application/helpers/guid_helper.php */