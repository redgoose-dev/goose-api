<?php
namespace Core;
use Exception;


/**
 * Goose
 *
 * @property string path
 * @property object config
 * @property Router router
 */

class Goose {

	public function __construct()
	{
		$this->path = null;
		$this->config = null;
		$this->router = new Router();
	}

	/**
	 * routing to controller
	 *
	 * @param string $target
	 * @param array $params
	 * @throws \Exception
	 */
	private function playController($target=null, $params=null)
	{
		try
		{
			// check $target
			if (!$target) throw new Exception('Not found target');
			if (!$this->path) throw new Exception('Not found path');

			// search controller
			$filePath = $this->path.'/controller/'.$target.'.php';
			print_r($filePath);
			print_r('========');
			var_dump(file_exists($filePath));
			//require $this->path.'/'.$target;

			//
//			print_r($target);
//			print_r($params);

		}
		catch(Exception $e)
		{
			// TODO: 404 page
		}
	}

	/**
	 * Play app trigger
	 *
	 * @param string $path
	 * @throws Exception
	 */
	public function run($path='')
	{
		// check install
		if (!file_exists(__DIR__.'/../data/config.php')) return;

		// set path
		$this->path = $path;

		// set config
		$this->config = require __DIR__.'/../data/config.php';

		// TODO: checking token

		// initialize routing
		$this->router->init();

		// play controller
		$this->playController($this->router->match['target'], $this->router->match['params']);
	}

}