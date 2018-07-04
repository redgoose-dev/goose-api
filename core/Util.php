<?php
namespace Core;
use Exception;

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

	/**
	 * check exist value
	 * 배열속에 필수값이 들어있는지 확인
	 *
	 * @param array $target 확인할 배열
	 * @param array $required 키값이 들어있는 배열
	 * @throws Exception
	 */
	public static function checkExistValue($target=null, $required=null)
	{
		if (!isset($target)) throw new Exception('No value `$target`');
		if ($required)
		{
			foreach ($required as $k=>$v)
			{
				if (!array_key_exists($v, $target) || !$target[$v])
				{
					throw new Exception('Can not find `'.$v.'`.');
				}
			}
		}
	}

	/**
	 * allow string
	 *
	 * @param string $value
	 * @param string $type
	 * @return boolean
	 */
	public static function allowString($value, $type='string_number')
	{
		switch ($type)
		{
			case 'string':
				return !!preg_match("/^[a-zA-Z]+$/", $value);

			case 'number':
				return !!preg_match("/^[0-9]+$/", $value);

			case 'string_number':
				return !!preg_match("/^[a-zA-Z0-9_-]+$/", $value);

			default:
				return false;
		}
	}

	/**
	 * get form data
	 *
	 * @return array
	 */
	public static function getFormData()
	{
		parse_str(file_get_contents('php://input'), $value);
		return $value;
	}
}