<?php
namespace Controller;
use Core\Goose, Core\Connect, Core\Message, Core\Auth;
use Exception;

class Main {

  /**
   * get items
   *
   * @param Goose|Connect $self
   * @param object $op
   * @param callable $callback
   * @return object
   * @throws Exception
   */
  public static function index(Goose|Connect $self, object $op, callable $callback = null): object
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
        Message::make('error.noValue', 'object', 'Controller\Main::index()'),
        500
      );
    }

    // get values
    $output = (object)[];
    $page = (int)($self->get->page ?? 1);
    $size = (int)($self->get->size ?? $_ENV['API_DEFAULT_INDEX_SIZE']);

    // get total
    $total = $self->model->getCount((object)[
      'table' => $op->table,
      'where' => $op->where,
      'debug' => $op->debug ?? __API_DEBUG__,
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

    // set op
    $opts = (object)[
      'table' => $op->table,
      'field' => $op->field ?? ($self->get->field ?? ''),
      'json_field' => $op->json_field ?? ($self->get->json_field ?? []),
      'order' => $op->order ?? ($self->get->order ?? ''),
      'sort' => $op->sort ?? ($self->get->sort ?? ''),
      'limit' => $limit,
      'where' => $op->where,
      'debug' => $op->debug ?? __API_DEBUG__,
    ];

    // get items
    $items = $self->model->getItems($opts);

    // 필요하면 산출된 데이터를 조정하기 위하여 콜백으로 한번 보낸다.
    if (is_callable($callback)) $items = $callback($items);

    // set output
    if (!(isset($op->object) && $op->object))
    {
      $output->code = 200;
      $output->query = $items->query;
      $output->data = (object)[
        'total' => $total->data,
        'index' => $items->data,
      ];
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
   * @param Goose|Connect $self
   * @param object $op
   * @param callable $callback
   * @return object
   * @throws Exception
   */
  public static function item(Goose|Connect $self, object $op, callable $callback = null)
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
        Message::make('error.noValue', 'object', 'Controller\Main::item()'),
        500
      );
    }

    // get values
    $output = (object)[];

    // get data
    $item = $self->model->getItem((object)[
      'table' => $op->table,
      'field' => $op->field ?? ($self->get->field ?? ''),
      'json_field' => $op->json_field ?? [],
      'where' => ($op->srl ? 'srl='.(int)$op->srl : ($op->id ? "id='$op->id'" : '')).($op->where ?? ''),
      'debug' => $op->debug ?? __API_DEBUG__,
    ]);

    // 필요하면 산출된 데이터를 조정하기 위하여 콜백으로 한번 보낸다.
    if (is_callable($callback)) $item = $callback($item);

    // set output
    $output->code = $item->data ? 200 : 404;
    $output->query = $item->query;
    $output->data = $item->data ?? null;

    return $output;
  }

  /**
   * add item
   *
   * @param Goose|Connect $self
   * @param object $op
   * @return object
   * @throws Exception
   */
  public static function add(Goose|Connect $self, object $op): object
  {
    if (!($op->table && $op->data))
    {
      throw new Exception(
        Message::make('error.noValue', 'object', 'Controller\Main::add()'),
        500
      );
    }

    // get values
    $output = (object)[];

    // add data
    $result = $self->model->add((object)[
      'table' => $op->table,
      'data' => $op->data,
      'debug' => $op->debug ?? __API_DEBUG__,
    ]);

    // set output
    $output->code = 200;
    $output->query = $result->query;
    $output->srl = $self->model->getLastIndex();

    // set return
    if ($op->return ?? false)
    {
      $output->data = $self->model->getItem((object)[
        'table' => $op->table,
        'where' => 'srl='.(int)$output->srl,
      ])->data;
    }

    return $output;
  }

  /**
   * edit item
   *
   * @throws Exception
   */
  public static function edit(Goose|Connect $self, object $op): object
  {
    if (!($op->table && $op->srl && $op->data))
    {
      throw new Exception(
        Message::make('error.noValue', 'object', 'Controller\Main::edit()'),
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
      'debug' => $op->debug ?? __API_DEBUG__,
    ]);

    // set output
    $output->code = 200;
    $output->query = $result->query;

    return $output;
  }

  /**
   * delete item
   * @throws Exception
   */
  public static function delete(Goose|Connect $self, object $op): object
  {
    /**
     * $op guide
     * @param string $op->table
     * @param int $op->srl
     */
    // check $op
    if (!($op->table ?? $op->srl ?? false))
    {
      throw new Exception(
        Message::make('error.noValue', 'object', 'Controller\Main::delete()'),
        500
      );
    }
    // get values
    $output = (object)[];
    // delete data
    $result = $self->model->delete((object)[
      'table' => $op->table,
      'where' => 'srl='.(int)$op->srl,
      'debug' => $op->debug ?? __API_DEBUG__,
    ]);
    // set output
    $output->code = 200;
    $output->query = $result->query;
    // result
    return $output;
  }

  /**
   * check access item
   * 하나의 데이터를 가져오면서 접근할 수 있는지 검사한다.
   * 하는김에 데이터를 가져오고 토큰 검사하면서 토큰 decode값을 가져오면서 리턴해준다.
   * @throws Exception
   */
  public static function checkAccessItem(Goose|Connect $self, object $op): object
  {
    /**
     * $op guide
     *
     * @param string $op->table
     * @param int $op->srl
     * @param string $op->id
     * @param boolean $op->useStrict  getItem 상황이라면 꼭 사용한다.
     */

    // check parameter
    if (!($op->table && ($op->srl || $op->id)))
    {
      throw new Exception(Message::make('msg.noParams'), 500);
    }
    // strict 검사를 하면서 `strict`값이 없을때..
    if (($op->useStrict ?? false) && !($self->get->strict ?? $self->post->strict ?? null))
    {
      return Auth::checkAuthorization($self->model, '');
    }

    // get data
    $res = $self->model->getItem((object)[
      'table' => $op->table,
      'field' => $op->field ?? 'user_srl',
      'where' => isset($op->srl) ? 'srl='.(int)$op->srl : ($op->id ? "id='$op->id'" : ''),
    ]);
    if (!$res->data)
    {
      throw new Exception(Message::make('error.noFrom', 'data', $op->table), 204);
    }

    // check authorization
    $token = Auth::checkAuthorization($self->model, 'user');
    if ($token->data->admin) return $token;
    // check data and user_srl
    if ((int)$token->data->srl !== (int)$res->data->user_srl)
    {
      throw new Exception(Message::make('msg.notAccessItem'), 401);
    }
    return $token;
  }

  /**
   * check access index
   *
   * @param Goose|Connect $self
   * @param bool $useStrict useStrict
   * @return object
   * @throws Exception
   */
  public static function checkAccessIndex(Goose|Connect $self, bool $useStrict = false): object
  {
    // `$op->useStrict`가 있는 상태에서 `strict=false` 이거나 $op->useStrict가 없으면 public
    $userType = (($useStrict && !($self->get->strict ?? null)) || !$useStrict) ? '' : 'user';
    return Auth::checkAuthorization($self->model, $userType);
  }

}
