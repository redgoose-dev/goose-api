<?php
namespace Core;
use Exception;


class Output {

	/**
	 * print data
	 *
	 * @param object|array obj
	 * @param string $format
	 */
	public static function data($result=null, $format='json')
	{
		header('Content-Type: application/json');

		if ($result)
		{
			// filtering query
			if (!__DEBUG__) unset($result->query);

			// filtering code
			switch ($result->code)
			{
				case 500:
					$result->message = (__DEBUG__) ? $result->message : 'Service error';
					break;
				case 401:
					$result->message = (__DEBUG__) ? $result->message : 'Authorization error';
					break;
				case 403:
					$result->message = (__DEBUG__) ? $result->message : 'Permission denied';
					break;
				case 404:
					$result->message = (__DEBUG__) ? $result->message : 'Not found data';
					break;
				case 0:
					$result->code = 500;
					break;
			}
		}
		else
		{
			$result = (object)[
				'code' => 500,
				'message' => 'Service error'
			];
		}

		// set processing time
		if (__DEBUG__ && __START_TIME__)
		{
			$endTime = microtime(true);
			$time = $endTime - __START_TIME__;
			$result->time = number_format($time,6) * 1000 . 'ms';
		}

		// print output
		switch ($format)
		{
			case 'text':
				break;

			case 'rss':
				break;

			case 'json':
			default:
				echo json_encode(
					$result,
					!$_GET['min'] ? JSON_PRETTY_PRINT : null
				);
				break;
		}

		exit;
	}

	/**
	 * print page
	 */
	public static function page()
	{
		echo 'print page';
	}

}