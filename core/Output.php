<?php
namespace Core;


class Output {

	/**
	 * print json
	 *
	 * @param object|array obj
	 * @param boolean $min
	 */
	public static function json($result=null, $minify=false)
	{
		header('Content-Type: application/json');

		if ($result)
		{
			echo json_encode(
				$result,
				!$minify ? JSON_PRETTY_PRINT : null
			);
		}
		else
		{
			echo json_encode(
				(object)[
					'message' => 'Unknown error',
					'code' => 500
				],
				!$minify ? JSON_PRETTY_PRINT : null
			);
		}

		exit;
	}

}