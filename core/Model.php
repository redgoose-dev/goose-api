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
		$this->prefix = null;
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
	 * @return string
	 */
	private function query($options=null)
	{
		if (!($options->act && $options->table)) return null;

		// filtering where
		$options->where = ($options->where) ? preg_replace("/^and|and$/", "", $options->where) : '';

		$str = $options->act;
		$str .= ($options->field) ? ' '.$options->field : ' *';
		$str .= ' from '.$this->getTableName($options->table);
		$str .= ($options->where) ? ' where '.$options->where : '';
		$str .= ($options->order) ? ' order by '.$options->order : '';
		$str .= ($options->sort) ? ' ' . (($options->sort === 'asc') ? 'asc' : 'desc') : '';
		if ($options->limit)
		{
			if (is_array($options->limit))
			{
				$options->limit[0] = ($options->limit[0]) ? $options->limit[0] : 0;
				$options->limit[1] = ($options->limit[1]) ? $options->limit[1] : 0;
				$str .= ' limit ' . implode(',', $options->limit);
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
	 * @param object $config
	 * @param string $prefix
	 * @return Exception
	 */
	public function connect($config=null, $prefix='goose_')
	{
		// set prefix
		$this->prefix = $prefix;

		try
		{
			$this->db = new PDO(
				'mysql:dbname='.$config->dbname.';host='.$config->host.';port='.$config->port,
				$config->name,
				$config->password
			);
			$this->action('set names utf8');
			return null;
		}
		catch(PDOException $e)
		{
			$message = (__DEBUG__) ? $e->getMessage() : 'Failed connect database';
			$code = (__DEBUG__) ? $e->getCode() : 500;
			return new Exception($message, $code);
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
		$this->db->query($query);
	}

	/**
	 * get count
	 *
	 * @param object $options
	 * @return string|int
	 */
	public function getCount($options=null)
	{
		$options->act = 'select';
		$options->field = 'count(*)';

		// make query
		$query = $this->query($options);

		// is debug
		if ($options->debug === true)
		{
			return $query;
		}

		// result
		$result = $this->db->prepare($query);
		$result->execute();
		return (int)$result->fetchColumn();
	}

	/**
	 * get items
	 *
	 * @param object $options
	 * @return string|array
	 */
	public function getItems($options=null)
	{
		$options->act = 'select';
		$options->field = ($options->field) ? $options->field : '*';

		// make query
		$query = $this->query($options);

		// is debug
		if ($options->debug === true)
		{
			return $query;
		}

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
			return $result ? $result : [];
		}
		else
		{
			return [];
		}
	}

	/**
	 * get item
	 *
	 * @param object $options
	 * @return object
	 */
	public function getItem($options=null)
	{
		$options->act = 'select';
		$options->field = ($options->field) ? $options->field : '*';

		// make query
		$query = $this->query($options);

		// is debug
		if ($options->debug === true)
		{
			return $query;
		}

		$qry = $this->db->query($query);
		if ($qry)
		{
			$result = $qry->fetch(PDO::FETCH_ASSOC);
			if ($result && $options->json_field && count($options->json_field))
			{
				$result = self::convertJsonToObject($result, $options->json_field);
			}
			return $result ? $result : null;
		}

		return null;
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

}