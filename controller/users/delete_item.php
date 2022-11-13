<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * delete user
 *
 * @var Goose|Connect $this
 */

try
{
  // check and set srl
  $srl = (int)($this->params['srl'] ?? 0);
  if ($srl <= 0)
  {
    throw new Exception(Message::make('error.notFound', 'srl'), 404);
  }

  // connect db
  $this->model->connect();

  // check data
  $cnt = $this->model->getCount((object)[
    'table' => 'users',
    'where' => 'srl='.$srl,
  ])->data;
  if ($cnt <= 0)
  {
    throw new Exception(Message::make('error.noData', 'user'), 204);
  }

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'admin');
  if (!$token->data->admin && ((int)$token->data->srl !== $srl))
  {
    throw new Exception(Message::make('error.access'), 401);
  }

  // remove item
  $output = Controller\Main::delete($this, (object)[
    'table' => 'users',
    'srl' => $srl,
  ]);

  // TODO: 사용자를 지우면 컨텐츠들을 지워야 할거 같은데 이건 좀 고려해보자..

  // set output
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output data
  return Output::result($output);
}
catch (Exception $e)
{
  if ($this->model ?? false) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
