<?php
namespace Core;
use PDO, Exception, PDOException, DateTimeZone, DateTime;


/**
 * Database model
 *
 * @property object db
 * @property string prefix
 */

class Model {

  public object|null $db;
  public string $prefix = 'goose_';

  public function __construct()
  {}

  /**
   * convert json to object
   */
  private static function convertJsonToObject(array|object $item, array $fields): object|array
  {
    foreach ($fields as $k=>$v)
    {
      if ($item->{$v} ?? false)
      {
        $item->{$v} = json_decode($item->{$v}, false);
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
  private function query(object $op): string
  {
    if (!(($op->act ?? false) && ($op->table ?? false))) return '';
    // filtering where
    if ($op->where ?? false)
    {
      $op->where = preg_replace("/^ and/", "", $op->where);
      $op->where = trim($op->where);
    }
    // set keyword
    $str = $op->act;
    $str .= ($op->field ?? false) ? ' '.$op->field : ' *';
    $str .= ' from '.$this->getTableName($op->table);
    $str .= ($op->where ?? false) ? ' where '.$op->where : '';
    $str .= ($op->order ?? false) ? ' order by '.$op->order : '';
    $str .= (($op->order ?? false) && ($op->sort ?? false) && $op->order && $op->sort) ? ' ' . ((($op->sort ?? false) && $op->sort === 'asc') ? 'asc' : 'desc') : '';
    if ($op->limit ?? false)
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
  public function connect(): void
  {
    try
    {
      $this->db = new PDO(
        'mysql:dbname='.$_ENV['API_DB_DATABASE'].';host='.$_ENV['API_DB_HOST'].';port='.$_ENV['API_DB_PORT'],
        $_ENV['API_DB_USERNAME'],
        $_ENV['API_DB_PASSWORD']
      );
      $this->action('set names utf8mb4');
      if ($_ENV['API_TIMEZONE_OFFSET'] ?? false)
      {
        $this->action("set session time_zone='$_ENV[API_TIMEZONE_OFFSET]'");
      }
    }
    catch(PDOException $e)
    {
      var_dump($e->getMessage());
      $message = (__API_DEBUG__) ? $e->getMessage() : 'Failed connect database';
      $code = (__API_DEBUG__) ? $e->getCode() : 500;
      throw new Exception($message, $code);
    }
  }

  /**
   * disconnect database
   */
  public function disconnect(): void
  {
    $this->db = null;
  }

  /**
   * get table name
   */
  public function getTableName(string $tableName): string
  {
    return $this->prefix.$tableName;
  }

  /**
   * run query
   *
   * @throws Exception
   */
  public function action(string $query): void
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
  public function getLastIndex(): int
  {
    return (int)$this->db->lastInsertId();
  }

  /**
   * get query command
   *
   * @param object $op
   * @return string
   */
  public function getQuery(object $op): string
  {
    return $this->query($op);
  }

  /**
   * get count
   */
  public function getCount(object $op): object
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
    $output->data = $result;
    if ($op->debug ?? false) $output->query = $query;
    // result
    return $output;
  }

  /**
   * get items
   */
  public function getItems(object $op): object
  {
    $output = (object)[];

    $op->act = 'select';
    $op->field = ($op->field ?? false) ? Util::convertFields($op->field) : '*';

    // make query
    $query = $this->query($op);

    // get data
    $qry = $this->db->query($query);
    if ($qry)
    {
      $result = $qry->fetchAll(PDO::FETCH_CLASS);
      if ($result && count($op->json_field ?? []) > 0)
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
    $output->data = $result ?? [];
    if ($op->debug ?? false) $output->query = $query;

    return $output;
  }

  /**
   * get item
   */
  public function getItem(object $op): object
  {
    $output = (object)[];

    $op->act = 'select';
    $op->field = ($op->field ?? false) ? Util::convertFields($op->field) : '*';

    // make query
    $query = $this->query($op);

    // get data
    $qry = $this->db->query($query);
    if ($qry)
    {
      $result = (object)$qry->fetch(PDO::FETCH_ASSOC);
      if (isset($result->scalar) && $result->scalar === false) $result = null;
      if ($result && ($op->json_field ?? null) && count($op->json_field))
      {
        $result = self::convertJsonToObject($result, $op->json_field);
      }
    }

    // set output
    $output->data = $result ? (object)$result : null;
    if ($op->debug ?? false) $output->query = $query;

    return $output;
  }

  /**
   * add item
   *
   * @param object $op
   * @return object
   * @throws Exception
   */
  public function add(object $op): object
  {
    // check $op
    if (!($op->table ?? false)) throw new Exception('Not found $op->table');
    if (!($op->data ?? false)) throw new Exception('Not found $op->data');

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
    if ($op->debug ?? false) $output->query = $query;
    $output->success = true;

    return $output;
  }

  /**
   * edit item
   *
   * @param object $op
   * @return object|null
   * @throws Exception
   */
  public function edit(object $op): object|null
  {
    // check $op
    if (!($op->data ?? false)) throw new Exception('Not found $op->data');

    // check exist data
    $cnt = $this->getCount((object)[
      'table' => $op->table,
      'where' => $op->where,
      'debug' => true,
    ]);
    if (!$cnt->data)
    {
      if ($op->continue ?? false) return null;
      throw new Exception('Not found data');
    }

    // make query
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
    if ($op->debug ?? false) $output->query = $query;
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
  public function delete(object $op): object
  {
    $output = (object)[];
    try
    {
      if (!($op->table ?? false)) throw new Exception('no value `table`');
      if (!($op->where ?? false)) throw new Exception('no value `where`');
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
      if ($op->debug ?? false) $output->query = $query;
      return $output;
    }
  }

  /**
   * get max in field
   */
  public function getMax(object $op): object
  {
    $output = (object)[];
    try
    {
      if (!($op->table ?? false)) throw new Exception('no value `table`');
      if (!($op->field ?? false)) throw new Exception('no value `field`');
      // set value
      $tableName = $this->getTableName($op->table);
      $field = $op->field ?? 'srl';
      // set query
      $query = 'select max('.$op->field.') as maximum from '.$tableName;
      $query .= ($op->where ?? false) ? ' where '.$op->where : '';
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
      if ($op->debug ?? false) $output->query = $query;
      return $output;
    }
  }

}
