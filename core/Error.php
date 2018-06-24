<?php
namespace Core;


class Error {

	public static function page()
	{
		// TODO
		Output::page();
	}

	/**
	 * data type error
	 *
	 * @param string $message
	 * @param int $code
	 */
	public static function data($message='Unknown error', $code=500)
	{
		Output::json((object)[
			'message' => $message,
			'code' => $code,
		], false);
		exit;
	}

}