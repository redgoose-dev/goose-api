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
    $this->prefix = 'goose_';
  }

  /**
   * convert json to object
   *
   * @param array $item
   * @param array $fields
   * @return object|array
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
    if (!(isset($op->act) && isset($op->table))) return null;

    // filtering where
    if (isset($op->where))
    {
      $op->where = preg_replace("/^ and/", "", $op->where);
      $op->where = trim($op->where);
    }

    $str = $op->act;
    $str .= (isset($op->field)) ? ' '.$op->field : ' *';
    $str .= ' from '.$this->getTableName($op->table);
    $str .= (isset($op->where) && $op->where) ? ' where '.$op->where : '';
    $str .= (isset($op->order) && $op->order) ? ' order by '.$op->order : '';
    $str .= (isset($op->order) && isset($op->sort) && $op->order && $op->sort) ? ' ' . ((isset($op->sort) && $op->sort && $op->sort === 'asc') ? 'asc' : 'desc') : '';

    if (isset($op->limit) && $op->limit)
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
        'mysql:dbname='.$_ENV['API_DB_DATABASE'].';host='.$_ENV['API_DB_HOST'].';port='.$_ENV['API_DB_PORT'],
        $_ENV['API_DB_USERNAME'],
        $_ENV['API_DB_PASSWORD']
      );
      $this->action('set names utf8');
    }
    catch(PDOException $e)
    {
      $message = (__API_DEBUG__) ? $e->getMessage() : 'Failed connect database';
      $code = (__API_DEBUG__) ? $e->getCode() : 500;
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
    $op->field = ($op->field) ? Util::convertFields($op->field) : '*';

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
    $output->data = isset($result) ? $result : [];
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
    $op->field = ($op->field) ? Util::convertFields($op->field) : '*';

    // make query
    $query = $this->query($op);

    // get data
    $qry = $this->db->query($query);
    if ($qry)
    {
      $result = (object)$qry->fetch(PDO::FETCH_ASSOC);
      if ($result && isset($result->scalar) && !$result->scalar) $result = null;
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
   * @throws Exception
   */
  public function add($op=null)
  {
    // check $op
    if (!isset($op->table)) throw new Exception('Not found $op->table');
    if (!isset($op->data)) throw new Exception('Not found $op->data');

    // set query
    $query = '';
    $query = 'insert into `'.$this->getTableName($op->table).'` ';

    // set keys
    $str = '';
    foreach ($op->data as $k=>$v)
    {
      $str .= ',`'.$k.'`';
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
   * @throws Exception
   */
  public function delete($op)
  {
    $output = (object)[];
    try
    {
      if (!isset($op->table)) throw new Exception('no value `table`');
      if (!isset($op->where)) throw new Exception('no value `where`');
      // check exist data
      $cnt = $this->getCount((object)[
        'table' => $op->table,
        'where' => $op->where
      ]);
      if ($cnt->data)
      {
        // set query
        $query = "delete from ".$this->getTableName($op->table)." where $op->where";
        // action
        $this->action($query);
        $output->success = true;
      }
      else
      {
        $output->success = false;
      }
    }
    catch(Exception $e)
    {
      throw new Exception($e->getMessage());
    }
    finally
    {
      // set output
      if ($op->debug === true) $output->query = $query;
      return $output;
    }
  }

  /**
   * get max in field
   *
   * @param object $op
   * @return int
   * @throws Exception
   */
  public function getMax($op=null)
  {
    $output = (object)[];
    try
    {
      if (!isset($op->table)) throw new Exception('no value `table`');
      if (!isset($op->field)) throw new Exception('no value `field`');
      // set value
      $tableName = $this->getTableName($op->table);
      $field = isset($op->field) ? $op->field : 'srl';
      // set query
      $query = 'select max('.$op->field.') as maximum from '.$tableName;
      if (isset($op->where)) $query .= ' where '.$op->where;
      // action
      $max = $this->db->prepare($query);
      $max->execute();
      $max = (int)$max->fetchColumn();
      // set output
      $output->success = true;
      $output->data = $max;
      return $output;
    }
    catch(Exception $e)
    {
      throw new Exception($e->getMessage());
    }
    finally
    {
      // set output
      if ($op->debug === true) $output->query = $query;
      return $output;
    }
  }

}
