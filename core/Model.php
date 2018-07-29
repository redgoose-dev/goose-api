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
		$this->prefix = getenv('DEFAULT_TABLE_PREFIX');
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
			if ($item->{$v})
			{
				$item->{$v} = json_decode(urldecode($item->{$v}), false);
			}
		}
		return $item;
	}

	/**
	 * query maker
	 *
	 * @param object $op
	 * @param string $op->act
	 * @param string $op->where
	 * @param string $op->field
	 * @param string $op->table
	 * @param string $op->order
	 * @param string $op->sort
	 * @param array $op->limit
	 * @return string
	 */
	private function query($op=null)
	{
		if (!($op->act && $op->table)) return null;

		// filtering where
		if ($op->where)
		{
			$op->where = preg_replace("/^ and/", "", $op->where);
			$op->where = trim($op->where);
		}

		$str = $op->act;
		$str .= ($op->field) ? ' '.$op->field : ' *';
		$str .= ' from '.$this->getTableName($op->table);
		$str .= ($op->where) ? ' where '.$op->where : '';
		$str .= ($op->order) ? ' order by '.$op->order : '';
		$str .= ($op->order && $op->sort) ? ' ' . (($op->sort === 'asc') ? 'asc' : 'desc') : '';

		if ($op->limit)
		{
			if (is_array($op->limit))
			{
				if (count($op->limit) > 0)
				{
					$str .= ' limit ' . implode(',', $op->limit);
				}
			}
			else
			{
				$str .= ' limit ' . $op->limit;
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
	 * @throws Exception
	 */
	public function action($query)
	{
		if (!$this->db->query($query))
		{
			throw new Exception('Failed db action `'.$query.'`');
		}
	}

	/**
	 * get last index
	 *
	 * @return int
	 */
	public function getLastIndex()
	{
		return (int)$this->db->lastInsertId();
	}

	/**
	 * get query command
	 *
	 * @param object $op
	 * @return string
	 */
	public function getQuery($op=null)
	{
		return $this->query($op);
	}

	/**
	 * get count
	 *
	 * @param object $op
	 * @return object
	 */
	public function getCount($op=null)
	{
		$output = (object)[];

		$op->act = 'select';
		$op->field = 'count(*)';

		// make query
		$query = $this->query($op);

		// result
		$result = $this->db->prepare($query);
		$result->execute();
		$result = (int)$result->fetchColumn();

		// set output
		$output->data = $result ? $result : 0;
		if ($op->debug === true) $output->query = $query;

		return $output;
	}

	/**
	 * get items
	 *
	 * @param object $op
	 * @return object
	 */
	public function getItems($op=null)
	{
		$output = (object)[];

		$op->act = 'select';
		$op->field = ($op->field) ? $op->field : '*';

		// make query
		$query = $this->query($op);

		// get data
		$qry = $this->db->query($query);
		if ($qry)
		{
			$result = $qry->fetchAll(PDO::FETCH_CLASS);
			if ($result && $op->json_field && count($op->json_field))
			{
				foreach ($result as $k=>$v)
				{
					if ($json = self::convertJsonToObject($v, $op->json_field))
					{
						$result[$k] = $json;
					}
				}
			}
		}

		// set output
		$output->data = $result ? $result : [];
		if ($op->debug === true) $output->query = $query;

		return $output;
	}

	/**
	 * get item
	 *
	 * @param object $op
	 * @return object
	 */
	public function getItem($op=null)
	{
		$output = (object)[];

		$op->act = 'select';
		$op->field = ($op->field) ? $op->field : '*';

		// make query
		$query = $this->query($op);

		// get data
		$qry = $this->db->query($query);
		if ($qry)
		{
			$result = (object)$qry->fetch(PDO::FETCH_ASSOC);
			if ($result && $op->json_field && count($op->json_field))
			{
				$result = self::convertJsonToObject($result, $op->json_field);
			}
		}

		// set output
		$output->data = $result ? (object)$result : null;
		if ($op->debug === true) $output->query = $query;

		return $output;
	}

	/**
	 * add item
	 *
	 * @param object $op
	 * @return object
	 */
	public function add($op=null)
	{
		// check $op
		if (!isset($op->table)) throw new Exception('Not found $op->table');
		if (!isset($op->data)) throw new Exception('Not found $op->data');

		// set query
		$query = '';
		$query = 'insert into '.$this->getTableName($op->table).' ';

		// set keys
		$str = '';
		foreach ($op->data as $k=>$v)
		{
			$str .= ','.$k;
		}
		$str = preg_replace("/^,/", "", $str);
		$query .= '('.$str.')';

		$query .= ' values ';

		// set values
		$str = '';
		foreach ($op->data as $k=>$v)
		{
			$v = (!is_null($v)) ? '\''.$v.'\'' : 'null';
			$str .= ','.$v;
		}
		$str = preg_replace("/^,/", "", $str);
		$query .= '('.$str.')';

		// action
		$this->action($query);

		// set output
		$output = (object)[];
		if ($op->debug === true) $output->query = $query;
		$output->success = true;

		return $output;
	}

	/**
	 * edit item
	 *
	 * @param object $op
	 * @return object
	 * @throws Exception
	 */
	public function edit($op=null)
	{
		// check $op
		if (!isset($op->data)) throw new Exception('Not found $op->data');

		// check exist data
		$cnt = $this->getCount((object)[
			'table' => $op->table,
			'where' => $op->where,
			'debug' => true,
		]);
		if (!$cnt->data) throw new Exception('Not found data');

		// make query
		$query = '';
		$query = 'update '.$this->getTableName($op->table).' set ';
		$query_data = '';
		foreach ($op->data as $k=>$v)
		{
			$query_data .= ($v) ? ','.$v : '';
		}
		$query_data = preg_replace("/^,/", "", $query_data);
		$query .= $query_data;
		$query .= ' where ' . $op->where;

		// action
		$this->action($query);

		// set output
		$output = (object)[];
		if ($op->debug === true) $output->query = $query;
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
		if (!isset($op->table)) throw new Exception('no value `table`');
		if (!isset($op->where)) throw new Exception('no value `where`');

		// set value
		$output = (object)[];

		// check exist data
		$cnt = $this->getCount((object)[
			'table' => $op->table,
			'where' => $op->where
		]);
		if (!$cnt->data) throw new Exception('Not found data');

		// set query
		$query = "delete from ".$this->getTableName($op->table)." where $op->where";

		// action
		$this->action($query);

		// set output
		if ($op->debug === true) $output->query = $query;
		$output->success = true;

		return $output;
	}
}