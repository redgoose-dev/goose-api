<?php
namespace Core;


class Error {

	/**
	 * data type error
	 *
	 * @param string $message
	 * @param int $code
	 * @return null
	 */
	public static function data($message='Unknown error', $code=500)
	{
		Output::data((object)[
			'message' => $message,
			'code' => $code,
		]);
		return null;
	}

}