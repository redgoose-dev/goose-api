<?php
namespace Core;


/**
 * Util
 */

class util {

	/**
	 * get url parameter
	 *
	 * @param string $str
	 * @return string
	 */
	public static function getParameter($str='')
	{
		if ($_POST[$str])
		{
			return $_POST[$str];
		}
		else if ($_GET[$str])
		{
			return $_GET[$str];
		}
		else
		{
			return null;
		}
	}

	/**
	 * filtering where string
	 *
	 * @param string $str
	 * @return string
	 */
	public static function filteringWhere($str='')
	{
		$str = preg_replace("/^ and/", "", $str);
		return trim($str);
	}

}