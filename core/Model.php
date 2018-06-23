<?php
namespace Core;
use Exception;


/**
 * Database model
 *
 * @property object core
 */

class Model {

	public function __construct($type='mysql')
	{
		switch ($type)
		{
			case 'mysql':
				$this->core = new Database\Mysql();
				break;

			default:
				$this->core = null;
				break;
		}
	}

	private function check($options)
	{
		if (!$this->core)
		{
			return 'Not found database';
		}

		if (!$options)
		{
			return 'Not found options';
		}

		return null;
	}

	private static function error($message='Unknown error')
	{
		return new Exception($message);
	}

	/**
	 * connect database
	 *
	 * @return boolean
	 */
	public function connect()
	{
		return true;
	}

	/**
	 * disconnect database
	 */
	public function disconnect()
	{
		//
	}

	/**
	 * get count
	 *
	 * @param object $options
	 * @return int
	 */
	public function getCount($options=null)
	{
		if ($checkMessage = $this->check($options)) return self::error($checkMessage);

		return 0;
	}

	/**
	 * get items
	 *
	 * @param object $options
	 * @return array
	 */
	public function getItems($options=null)
	{
		if ($checkMessage = $this->check($options)) return self::error($checkMessage);

		return [];
	}

	/**
	 * get item
	 *
	 * @param object $options
	 * @return object
	 */
	public function getItem($options=null)
	{
		if ($checkMessage = $this->check($options)) return self::error($checkMessage);

		return (object)[];
	}

}