<?php
namespace Core;

class Output {

	/**
	 * print data
	 *
	 * @param object|array obj
	 */
	public static function data($result=null)
	{
		if ($result)
		{
			// filtering query
			if (!__DEBUG__) unset($result->query);

			// set success
			$result->success = false;

			// filtering code
			switch ($result->code)
			{
				case 204:
					$result->message = $result->message ? $result->message : 'custom message';
					break;
				case 401:
					$result->message = ($result->message && __DEBUG__) ? $result->message : 'Authorization error';
					break;
				case 403:
					$result->message = ($result->message && __DEBUG__) ? $result->message : 'Permission denied';
					break;
				case 404:
					$result->message = ($result->message && __DEBUG__) ? $result->message : 'Not found data';
					break;
				case 200:
					$result->success = true;
					break;
				default:
					$result->code = 500;
					$result->message = ($result->message && __DEBUG__) ? $result->message : 'Service error';
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

		if (__DEBUG__)
		{
			$result->url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		}

		// set processing time
		if (__DEBUG__ && __START_TIME__)
		{
			$endTime = microtime(true);
			$time = $endTime - __START_TIME__;
			$result->time = number_format($time,6) * 1000 . 'ms';
		}

		// print output
		echo json_encode(
			$result,
			!isset($_GET['min']) ? JSON_PRETTY_PRINT : null
		);
		exit;
	}

}