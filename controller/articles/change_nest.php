<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * change nest
 * 선택된 article 데이터에서 `nest_srl`값을 변경하는 역할을 한다.
 * 변경된 `nest_srl`값에의해 `category_srl`, `app_srl` 값이 변하게 된다.
 *
 * @var Goose|Connect $this
 */

try
{
  // check and set srl
  $srl = (int)$this->params['srl'];
  if (!($srl && $srl > 0))
  {
    throw new Exception(Message::make('error.notFound', 'srl'));
  }

  // check post values
  Util::checkExistValue($this->post, ['nest_srl']);

  // set srl
  $nest_srl = (int)$this->post->nest_srl;

  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessItem($this, (object)[
    'table' => 'articles',
    'srl' => $srl,
  ]);

  /**
   * get nest
   *
   * 바뀐 `nest_srl`값으로 `nest` 데이터를 가져온다.
   * 만약 데이터가 없으면 오류를 일으키고 작업이 종료된다.
   */
  $nest = $this->model->getItem((object)[
    'table' => 'nests',
    'where' => 'srl='.$nest_srl,
  ]);
  if (!isset($nest->data))
  {
    throw new Exception(Message::make('error.noItem', 'nest data'));
  }
  else
  {
    $nest = $nest->data;
  }

  /**
   * set app_srl
   *
   * 바뀐 `nest`값에서 `app_srl`값을 가져와서 적용한다.
   */
  $app_srl = (isset($nest->app_srl) && $nest->app_srl) ? (int)$nest->app_srl : null;

  /**
   * set category_srl
   *
   * `$this->post->category_srl`값이 있으면 변경하기 위하여 실제로 데이터가 존재하는지 조회해본다.
   * 데이터가 없거나 `$this->post->category_srl`값이 없으면 `null`로 정의한다.
   */
  $category_srl = isset($this->post->category_srl) ? (int)$this->post->category_srl : null;
  if ($category_srl)
  {
    $cnt = $this->model->getCount((object)[
      'table' => 'categories',
      'where' => 'nest_srl='.$nest_srl.' and srl='.$category_srl,
    ]);
    $category_srl = ($cnt->data > 0) ? $category_srl : null;
  }

  // set output
  $output = Controller\Main::edit($this, (object)[
    'table' => 'articles',
    'srl' => $srl,
    'data' => [
      '`app_srl`='.($app_srl ? $app_srl : 'null'),
      '`category_srl`='.($category_srl ? $category_srl : 'null'),
      '`nest_srl`='.$nest_srl,
      "`modate`='".date("Y-m-d H:i:s")."'",
    ],
  ]);

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output data
  return Output::data($output);
}
catch(Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::data($e->getMessage(), $e->getCode());
}
