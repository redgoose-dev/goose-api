<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit checklist item
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
  Util::checkExistValue($this->post, [ 'content' ]);

  // set percent from content
  $percent = 0;
  // TODO: 내용을 수정하면 체크박스 갯수를 알아보고 퍼센테이지 값 도출해내야 한다.

  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessItem($this, (object)[
    'table' => 'checklist',
    'srl' => $srl,
  ]);

  // set output
  $output = Controller\Main::edit($this, (object)[
    'table' => 'checklist',
    'srl' => $srl,
    'data' => [
      "content='{$this->post->content}'",
      "percent={$percent}",
    ],
  ]);

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output data
  return Output::data($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::data($e->getMessage(), $e->getCode());
}
