<?php
namespace Core;
use eftec\bladeone;


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

	/**
	 * render page
	 *
	 * @param string $name
	 * @param array $props
	 */
	public static function page($name=null, $props=[])
	{
		// set blade
		define("BLADEONE_MODE", 0); // (optional) 1=forced (test),2=run fast (production), 0=automatic, default value.
		$blade = new bladeone\BladeOne(__PATH__.'/view', __PATH__.'/data/cache');
		echo $blade->run($name, $props);

		exit;
	}

}