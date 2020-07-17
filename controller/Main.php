<?php
namespace Controller;
use Exception;
use Core;


class Main {

  /**
   * get items
   *
   * @param object $op
   * @param callable $callback
   * @return object
   * @throws Exception
   */
  public static function index($op=null, callable $callback=null)
  {
    /**
     * $op guide
     * @param Core\Model $op->model
     * @param string $op->table
     * @param string $op->where
     * @param string $op->field
     * @param array $op->json_field
     * @param boolean $op->object 객체만 필요할때가 있어서 사용하면 결과값만 나온 객체만 리턴한다.
     *
     * url params guide
     * @param string order
     * @param string sort
     * @param string limit
     */

    if (!$op->table)
    {
      throw new Exception(
        Core\Message::make('error.noValue', 'object', 'Controller\Main::index()'),
        500
      );
    }
    if (!$op->model)
    {
      throw new Exception(
        Core\Message::make('error.notFound', '$op->model'),
        500
      );
    }

    // get values
    $output = (object)[];
    $page = ($_GET['page']) ? (int)$_GET['page'] : 1;
    $size = ($_GET['size']) ? (int)$_GET['size'] : $_ENV['DEFAULT_INDEX_SIZE'];

    // set model
    $model = $op->model;

    // get total
    $total = $model->getCount((object)[
      'table' => $op->table,
      'where' => $op->where,
      'debug' => (isset($op->debug)) ? $op->debug : __DEBUG__,
    ]);

    // set limit
    $limit = null;
    if (isset($_GET['unlimit']))
    {
      $limit = '';
    }
    else if (isset($_GET['limit']))
    {
      $limit = explode(',', $_GET['limit']);
    }
    else if (isset($_GET['page']) || isset($_GET['size']))
    {
      $limit = [ ($page - 1) * $size, $size ];
    }
    else
    {
      $limit = (int)$_ENV['DEFAULT_INDEX_SIZE'];
    }

    // get data
    $opts = (object)[
      'table' => $op->table,
      'field' => isset($op->field) ? $op->field : $_GET['field'],
      'json_field' => $op->json_field,
      'order' => isset($op->order) ? $op->order : $_GET['order'],
      'sort' => isset($op->sort) ? $op->sort : $_GET['sort'],
      'limit' => $limit,
      'where' => $op->where,
      'debug' => (isset($op->debug)) ? $op->debug : __DEBUG__,
    ];
    $items = $model->getItems($opts);

    // 필요하면 산출된 데이터를 조정하기 위하여 콜백으로 한번 보낸다.
    if (is_callable($callback)) $items = $callback($items);

    // set output
    if (!(isset($op->object) && $op->object))
    {
      $output->code = $total->data ? 200 : 404;
      $output->query = $items->query;
      if ($total->data)
      {
        $output->data = (object)[
          'total' => $total->data,
          'index' => $items->data,
        ];
      }
    }
    else
    {
      $output->total = $total->data;
      $output->index = $items->data;
      if (isset($items->query)) $output->query = $items->query;
    }

    return $output;
  }

  /**
   * get item
   *
   * @param object $op
   * @param callable $callback
   * @return object
   * @throws Exception
   */
  public static function item($op=null, callable $callback=null)
  {
    /**
     * $op guide
     * @param Core\Model $op->model
     * @param string $op->table
     * @param int $op->srl
     * @param string $op->id
     * @param array $op->json_field
     * @param string $op->where
     *
     * url params guide
     * @param string field
     */

    if (!($op->table && ($op->srl || $op->id)))
    {
      throw new Exception(
        Core\Message::make('error.noValue', 'object', 'Controller\Main::item()'),
        500
      );
    }
    if (!$op->model)
    {
      throw new Exception(
        Core\Message::make('error.notFound', '$op->model'),
        500
      );
    }

    // get values
    $output = (object)[];

    // set model
    $model = $op->model;

    // get data
    $item = $model->getItem((object)[
      'table' => $op->table,
      'field' => $_GET['field'],
      'json_field' => $op->json_field,
      'where' => ($op->srl ? 'srl='.(int)$op->srl : ($op->id ? "id='$op->id'" : '')).$op->where,
      'debug' => (isset($op->debug)) ? $op->debug : __DEBUG__,
    ]);

    // 필요하면 산출된 데이터를 조정하기 위하여 콜백으로 한번 보낸다.
    if (is_callable($callback)) $item = $callback($item);

    // set output
    $output->code = $item->data ? 200 : 404;
    $output->query = $item->query;
    if ($item->data) $output->data = $item->data;

    return $output;
  }

  /**
   * add item
   *
   * @param object $op
   * @return object
   * @throws Exception
   */
  public static function add($op=null)
  {
    if (!$op->table || !$op->data)
    {
      throw new Exception(
        Core\Message::make('error.noValue', 'object', 'Controller\Main::add()'),
        500
      );
    }

    // get values
    $output = (object)[];

    // set model
    $model = $op->model;

    // add data
    $result = $model->add((object)[
      'table' => $op->table,
      'data' => $op->data,
      'debug' => (isset($op->debug)) ? $op->debug : __DEBUG__,
    ]);

    // set output
    $output->code = 200;
    $output->query = $result->query;
    $output->srl = $model->getLastIndex();

    return $output;
  }

  /**
   * edit item
   *
   * @param object $op
   * @return object
   * @throws Exception
   */
  public static function edit($op=null)
  {
    if (!$op->table || !$op->srl || !$op->data)
    {
      throw new Exception(
        Core\Message::make('error.noValue', 'object', 'Controller\Main::edit()'),
        500
      );
    }

    // get values
    $output = (object)[];

    // set model
    $model = $op->model;

    // update data
    $result = $model->edit((object)[
      'table' => $op->table,
      'where' => 'srl='.(int)$op->srl,
      'data' => $op->data,
      'debug' => (isset($op->debug)) ? $op->debug : __DEBUG__,
    ]);

    // set output
    $output->code = 200;
    $output->query = $result->query;

    return $output;
  }

  /**
   * delete item
   *
   * @param object $op
   * @return object
   * @throws Exception
   */
  public static function delete($op=null)
  {
    /**
     * $op guide
     * @param Core\Model $op->model
     * @param string $op->table
     * @param int $op->srl
     */

    if (!$op->table || !$op->srl)
    {
      throw new Exception(
        Core\Message::make('error.noValue', 'object', 'Controller\Main::delete()'),
        500
      );
    }

    // get values
    $output = (object)[];

    // set model
    $model = $op->model;

    // delete data
    $result = $model->delete((object)[
      'table' => $op->table,
      'where' => 'srl='.(int)$op->srl,
      'debug' => (isset($op->debug)) ? $op->debug : __DEBUG__,
    ]);

    // set output
    $output->code = 200;
    $output->query = $result->query;

    return $output;
  }

  /**
   * count item
   *
   * @param object $op
   * @return object
   * @throws Exception
   */
  public static function count($op=null)
  {
    if (!$op->table)
    {
      throw new Exception(
        Core\Message::make('error.noValue', 'object', 'Controller\Main::count()'),
        500
      );
    }
    if (!$op->model)
    {
      throw new Exception(
        Core\Message::make('error.notFound', '$op->model'),
        500
      );
    }

    // set model
    $model = $op->model;

    // get total
    $total = $model->getCount((object)[
      'table' => $op->table,
      'where' => $op->where,
      'debug' => (isset($op->debug)) ? $op->debug : __DEBUG__,
    ]);

    return $total->data;
  }

  /**
   * check access item
   * 하나의 데이터를 가져오면서 접근할 수 있는지 검사한다.
   * 하는김에 데이터를 가져오고 토큰 검사하면서 토큰 decode값을 가져오면서 리턴해준다.
   *
   * @param object $op
   * @return object token
   * @throws Exception
   */
  public static function checkAccessItem($op=null)
  {
    /**
     * $op guide
     *
     * @param Core\Model $op->model
     * @param string     $op->table
     * @param int        $op->srl
     * @param string     $op->id
     * @param boolean    $op->useStrict  getItem 상황이라면 꼭 사용한다.
     */

    // check parameter
    if (!($op->model && $op->table && ($op->srl || $op->id)))
    {
      throw new Exception(Core\Message::make('msg.noParams'), 500);
    }
    // strict 검사를 하면서 'strict'값이 없을때..
    if (!!$op->useStrict && !($_GET['strict'] || $_POST['strict']))
    {
      return Core\Auth::checkAuthorization($op->model);
    }

    // get data
    $res = $op->model->getItem((object)[
      'table' => $op->table,
      'field' => $op->field ? $op->field : 'user_srl',
      'where' => ($op->srl) ? 'srl='.(int)$op->srl : ($op->id ? "id='$op->id'" : ''),
    ]);
    if (!$res->data)
    {
      throw new Exception(Core\Message::make('error.noFrom', 'data', $op->table), 404);
    }

    // check authorization
    $token = Core\Auth::checkAuthorization($op->model, 'user');
    // check data and user_srl
    if (!$token->data->admin && ((int)$token->data->user_srl !== (int)$res->data->user_srl))
    {
      throw new Exception(Core\Message::make('msg.notAccessItem'), 401);
    }
    return $token;
  }

  /**
   * check access index
   *
   * @param Core\Model $model
   * @param boolean useStrict
   * @return object token
   * @throws Exception
   */
  public static function checkAccessIndex($model=null, $useStrict=false)
  {
    // `$op->useStrict`가 있는 상태에서 `strict=false` 이거나 $op->useStrict가 없으면 public
    $param = (($useStrict && !$_GET['strict']) || !$useStrict) ? '' : 'user';
    return Core\Auth::checkAuthorization($model, $param);
  }

}
