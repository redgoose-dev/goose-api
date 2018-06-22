<?php
namespace Core;


/**
 * @property string path
 * @property object config
 */

class Goose {

	public function __construct()
	{
		$this->path = null;
		$this->config = null;
	}

	/**
	 * Play app trigger
	 *
	 * @param string $path
	 */
	public function run($path='')
	{
		$this->path = $path;

		// check install
		if (true)
		{
			$this->config = require __DIR__.'/../data/config.php';
		}
		else
		{
			// not installed
		}

		print_r([
			'aa' => 'bb',
		]);
	}

}