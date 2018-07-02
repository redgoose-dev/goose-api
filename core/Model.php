<?php
namespace Core;
use PDO, Exception, PDOException;


/**
 * Database model
 *
 * @property object db
 * @property string prefix
 */

class Model {

	public function __construct()
	{
		$this->db = null;
		$this->prefix = getenv('TABLE_PREFIX');
	}

	/**
	 * convert json to object
	 *
	 * @param array $item
	 * @param array $fields
	 * @return object
	 */
	private static function convertJsonToObject($item, $fields)
	{
		foreach ($fields as $k=>$v)
		{
			if ($item[$v])
			{
				$item[$v] = json_decode(urldecode($item[$v]), false);
			}
		}
		return $item;
	}

	/**
	 * query maker
	 *
	 * @param object $options
	 * @param string $options->act
	 * @param string $options->where
	 * @param string $options->field
	 * @param string $options->table
	 * @param string $options->order
	 * @param string $options->sort
	 * @param array $options->limit
	 * @return string
	 */
	private function query($options=null)
	{
		if (!($options->act && $options->table)) return null;

		// filtering where
		if ($options->where)
		{
			$options->where = preg_replace("/^ and/", "", $options->where);
			$options->where = trim($options->where);
		}

		$str = $options->act;
		$str .= ($options->field) ? ' '.$options->field : ' *';
		$str .= ' from '.$this->getTableName($options->table);
		$str .= ($options->where) ? ' where '.$options->where : '';
		$str .= ($options->order) ? ' order by '.$options->order : '';
		$str .= ($options->order && $options->sort) ? ' ' . (($options->sort === 'asc') ? 'asc' : 'desc') : '';

		if ($options->limit)
		{
			if (is_array($options->limit))
			{
				if (count($options->limit) > 0)
				{
					$str .= ' limit ' . implode(',', $options->limit);
				}
			}
			else
			{
				$str .= ' limit ' . $options->limit;
			}
		}

		return $str;
	}

	/**
	 * connect database
	 *
	 * @throws Exception
	 */
	public function connect()
	{
		try
		{
			$this->db = new PDO(
				'mysql:dbname='.getenv('DB_DATABASE').';host='.getenv('DB_HOST').';port='.getenv('DB_PORT'),
				getenv('DB_USERNAME'),
				getenv('DB_PASSWORD')
			);
			$this->action('set names utf8');
		}
		catch(PDOException $e)
		{
			$message = (__DEBUG__) ? $e->getMessage() : 'Failed connect database';
			$code = (__DEBUG__) ? $e->getCode() : 500;
			throw new Exception($message, $code);
		}
	}

	/**
	 * disconnect database
	 */
	public function disconnect()
	{
		$this->db = null;
	}

	/**
	 * get table name
	 *
	 * @param string $tableName
	 * @return string
	 */
	public function getTableName($tableName=null)
	{
		return $this->prefix.$tableName;
	}

	/**
	 * run query
	 *
	 * @param string $query
	 */
	public function action($query)
	{
		$result = $this->db->query($query);
		if (!$result)
		{
			throw new Exception('Failed db action `'.$query.'`');
		}
	}

	/**
	 * get count
	 *
	 * @param object $options
	 * @return object
	 */
	public function getCount($options=null)
	{
		$output = (object)[];

		$options->act = 'select';
		$options->field = 'count(*)';

		// make query
		$query = $this->query($options);

		// result
		$result = $this->db->prepare($query);
		$result->execute();
		$result = (int)$result->fetchColumn();

		// set output
		$output->data = $result ? $result : 0;
		if ($options->debug === true) $output->query = $query;

		return $output;
	}

	/**
	 * get items
	 *
	 * @param object $options
	 * @return object
	 */
	public function getItems($options=null)
	{
		$output = (object)[];

		$options->act = 'select';
		$options->field = ($options->field) ? $options->field : '*';

		// make query
		$query = $this->query($options);

		// get data
		$qry = $this->db->query($query);
		if ($qry)
		{
			$result = $qry->fetchAll(PDO::FETCH_ASSOC);
			if ($result && $options->json_field && count($options->json_field))
			{
				foreach ($result as $k=>$v)
				{
					if ($json = self::convertJsonToObject($v, $options->json_field))
					{
						$result[$k] = $json;
					}
				}
			}
		}

		// set output
		$output->data = $result ? $result : [];
		if ($options->debug === true) $output->query = $query;

		return $output;
	}

	/**
	 * get item
	 *
	 * @param object $options
	 * @return object
	 */
	public function getItem($options=null)
	{
		$output = (object)[];

		$options->act = 'select';
		$options->field = ($options->field) ? $options->field : '*';

		// make query
		$query = $this->query($options);

		// get data
		$qry = $this->db->query($query);
		if ($qry)
		{
			$result = $qry->fetch(PDO::FETCH_ASSOC);
			if ($result && $options->json_field && count($options->json_field))
			{
				$result = self::convertJsonToObject($result, $options->json_field);
			}
		}

		// set output
		$output->data = $result ? (object)$result : null;
		if ($options->debug === true) $output->query = $query;

		return $output;
	}

	/**
	 * get query command
	 *
	 * @param object $options
	 * @return string
	 */
	public function getQuery($options=null)
	{
		return $this->query($options);
	}

	/**
	 * add item
	 *
	 * @param object $options
	 * @return object
	 */
	public function addItem($options=null)
	{
		$query = '';
		$result = null;
		$output = (object)[];

		if ($options->table && $options->data)
		{
			$query = 'insert into '.$this->getTableName($options->table).' ';

			// set keys
			$str = '';
			foreach ($options->data as $k=>$v)
			{
				$str .= ','.$k;
			}
			$str = preg_replace("/^,/", "", $str);
			$query .= '('.$str.')';

			$query .= ' values ';

			// set values
			$str = '';
			foreach ($options->data as $k=>$v)
			{
				//$str .= ','.$k;
				$v = (!is_null($v)) ? '\''.$v.'\'' : 'null';
				$str .= ','.$v;
			}
			$str = preg_replace("/^,/", "", $str);
			$query .= '('.$str.')';

			// action
			$this->action($query);
		}

		if ($options->debug === true) $output->query = $query;
		$output->success = true;

		return $output;
	}

	/**
	 * delete data
	 *
	 * @param object $op
	 * @return object
	 */
	public function delete($op)
	{
		if (!$op->table) throw new Exception('no value `table`');
		if (!$op->where) throw new Exception('no value `where`');

		// set value
		$output = (object)[];

		// set query
		$query = "delete from ".$this->getTableName($op->table)." where $op->where";

		// action
		$this->action($query);

		// set output
		if ($op->debug === true) $output->query = $query;
		$output->success = true;

		// set output
		return $output;
	}
}