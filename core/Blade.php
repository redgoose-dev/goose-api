<?php
namespace Core;
use eftec\bladeone;


class Blade {

	static $views = __DIR__ . '/views';
	static $cache = __DIR__ . '/cache';

	public function __construct()
	{
		//
		define("BLADEONE_MODE", 0); // (optional) 1=forced (test),2=run fast (production), 0=automatic, default value.
		$this->blade = new bladeone\BladeOne(self::$views, self::$cache);
	}

	/**
	 * print view
	 *
	 * @param string $view
	 * @param array $params
	 */
	public function run($view='', $params=[])
	{
		echo $this->blade->run($view, $params);
	}

}