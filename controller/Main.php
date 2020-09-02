<?php
namespace Controller;
use Exception;
use Core;


class Main {

  /**
   * get items
   *
   * @param Core\Goose|Core\Connect $self
   * @param object $op
   * @param callable $callback
   * @return object
   * @throws Exception
   */
  public static function index($self, object $op, callable $callback=null)
  {
    /**
     * $op guide
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

    // get values
    $output = (object)[];
    $page = ($self->get->page) ? (int)$self->get->page : 1;
    $size = ($self->get->size) ? (int)$self->get->size : $_ENV['API_DEFAULT_INDEX_SIZE'];

    // get total
    $total = $self->model->getCount((object)[
      'table' => $op->table,
      'where' => $op->where,
      'debug' => (isset($op->debug)) ? $op->debug : __API_DEBUG__,
    ]);

    // set limit
    $limit = null;
    if (isset($self->get->unlimit))
    {
      $limit = '';
    }
    else if (isset($self->get->limit))
    {
      $limit = explode(',', $self->get->limit);
    }
    else if (isset($self->get->page) || isset($self->get->size))
    {
      $limit = [ ($page - 1) * $size, $size ];
    }
    else
    {
      $limit = (int)$_ENV['API_DEFAULT_INDEX_SIZE'];
    }

    // get data
    $opts = (object)[
      'table' => $op->table,
      'field' => isset($op->field) ? $op->field : $self->get->field,
      'json_field' => isset($op->json_field) ? $op->json_field : $self->get->json_field,
      'order' => isset($op->order) ? $op->order : $self->get->order,
      'sort' => isset($op->sort) ? $op->sort : $self->get->sort,
      'limit' => $limit,
      'where' => $op->where,
      'debug' => (isset($op->debug)) ? $op->debug : __API_DEBUG__,
    ];
    $items = $self->model->getItems($opts);

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
   * @param Core\Goose|Core\Connect $self
   * @param object $op
   * @param callable $callback
   * @return object
   * @throws Exception
   */
  public static function item($self, object $op, callable $callback=null)
  {
    /**
     * $op guide
     * @param string $op->table
     * @param int $op->srl
     * @param string $op->id
     * @param array $op->json_field
     * @param string $op->where
     * @param string $op->field
     */

    if (!($op->table && ($op->srl || $op->id)))
    {
      throw new Exception(
        Core\Message::make('error.noValue', 'object', 'Controller\Main::item()'),
        500
      );
    }

    // get values
    $output = (object)[];

    // get data
    $item = $self->model->getItem((object)[
      'table' => $op->table,
      'field' => isset($op->field) ? $op->field : $self->get->field,
      'json_field' => isset($op->json_field) ? $op->json_field : $self->get->json_field,
      'where' => ($op->srl ? 'srl='.(int)$op->srl : ($op->id ? "id='$op->id'" : '')).$op->where,
      'debug' => (isset($op->debug)) ? $op->debug : __API_DEBUG__,
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
   * @param Core\Goose|Core\Connect $self
   * @param object $op
   * @return object
   * @throws Exception
   */
  public static function add($self, object $op)
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

    // add data
    $result = $self->model->add((object)[
      'table' => $op->table,
      'data' => $op->data,
      'debug' => (isset($op->debug)) ? $op->debug : __API_DEBUG__,
    ]);

    // set output
    $output->code = 200;
    $output->query = $result->query;
    $output->srl = $self->model->getLastIndex();

    return $output;
  }

  /**
   * edit item
   *
   * @param Core\Goose|Core\Connect $self
   * @param object $op
   * @return object
   * @throws Exception
   */
  public static function edit($self, object $op)
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

    // update data
    $result = $self->model->edit((object)[
      'table' => $op->table,
      'where' => 'srl='.(int)$op->srl,
      'data' => $op->data,
      'debug' => (isset($op->debug)) ? $op->debug : __API_DEBUG__,
    ]);

    // set output
    $output->code = 200;
    $output->query = $result->query;

    return $output;
  }

  /**
   * delete item
   *
   * @param Core\Goose|Core\Connect $self
   * @param object $op
   * @return object
   * @throws Exception
   */
  public static function delete($self, object $op)
  {
    /**
     * $op guide
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

    // delete data
    $result = $self->model->delete((object)[
      'table' => $op->table,
      'where' => 'srl='.(int)$op->srl,
      'debug' => (isset($op->debug)) ? $op->debug : __API_DEBUG__,
    ]);

    // set output
    $output->code = 200;
    $output->query = $result->query;

    return $output;
  }

  /**
   * check access item
   * 하나의 데이터를 가져오면서 접근할 수 있는지 검사한다.
   * 하는김에 데이터를 가져오고 토큰 검사하면서 토큰 decode값을 가져오면서 리턴해준다.
   *
   * @param Core\Goose|Core\Connect $self
   * @param object $op
   * @return object token
   * @throws Exception
   */
  public static function checkAccessItem($self, object $op)
  {
    /**
     * $op guide
     *
     * @param string     $op->table
     * @param int        $op->srl
     * @param string     $op->id
     * @param boolean    $op->useStrict  getItem 상황이라면 꼭 사용한다.
     */

    // check parameter
    if (!($op->table && ($op->srl || $op->id)))
    {
      throw new Exception(Core\Message::make('msg.noParams'), 500);
    }
    // strict 검사를 하면서 'strict'값이 없을때..
    if (!!$op->useStrict && !($self->get->strict || $self->post->strict))
    {
      return Core\Auth::checkAuthorization($self->model);
    }

    // get data
    $res = $self->model->getItem((object)[
      'table' => $op->table,
      'field' => $op->field ? $op->field : 'user_srl',
      'where' => ($op->srl) ? 'srl='.(int)$op->srl : ($op->id ? "id='$op->id'" : ''),
    ]);
    if (!$res->data)
    {
      throw new Exception(Core\Message::make('error.noFrom', 'data', $op->table), 404);
    }

    // check authorization
    $token = Core\Auth::checkAuthorization($self->model, 'user');
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
   * @param Core\Goose|Core\Connect $self
   * @param boolean useStrict
   * @return object token
   * @throws Exception
   */
  public static function checkAccessIndex($self, $useStrict=false)
  {
    // `$op->useStrict`가 있는 상태에서 `strict=false` 이거나 $op->useStrict가 없으면 public
    $userType = (($useStrict && !$self->get->strict) || !$useStrict) ? '' : 'user';
    return Core\Auth::checkAuthorization($self->model, $userType);
  }

}
