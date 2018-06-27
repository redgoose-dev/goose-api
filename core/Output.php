<?php
namespace Core;


class Output {

	/**
	 * print json
	 *
	 * @param object|array obj
	 * @param string $format
	 */
	public static function json($result=null, $format='json')
	{
		header('Content-Type: application/json');

		$output = (object)[];

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
				case 403:
					$result->message = (__DEBUG__) ? $result->message : 'Permission denied';
					break;
				case 404:
					$result->message = (__DEBUG__) ? $result->message : 'Not found data';
					break;
			}
		}
		else
		{
			$result = (object)[
				'message' => 'Service error',
				'code' => 500
			];
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

}