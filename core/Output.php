<?php
namespace Core;

class Output {

	/**
	 * print data
	 *
	 * @param object|array obj
	 * @param string $format
	 */
	public static function data($result=null, $format='json')
	{
		if ($_GET['format']) $format = $_GET['format'];

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
			$result->code = 500;
			$result = (object)[ 'message' => 'Service error' ];
		}

		$result->success = $result->code === 200; // set success
		$code = $result->code; // code 값을 삭제할 수 있으므로 다른 변수로 저장해둔다.
		if (!__DEBUG__) unset($result->code);
		$result->url = $_SERVER['PATH_URL'].$_SERVER['REQUEST_URI']; // set url

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
				http_response_code($code);
				header('Content-Type: application/json');
				echo json_encode(
					$result,
					!$_GET['min'] ? JSON_PRETTY_PRINT : null
				);
				break;
		}
		exit;
	}

}