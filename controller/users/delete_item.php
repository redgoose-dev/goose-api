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
  $srl = (int)$this->params['srl'];
  if (!($srl && $srl > 0))
  {
    throw new Exception(Message::make('error.notFound', 'srl'));
  }

  // connect db
  $this->model->connect();

  // check data
  $cnt = $this->model->getCount((object)[
    'table' => 'users',
    'where' => 'srl='.$srl,
  ]);
  if (!$cnt->data)
  {
    throw new Exception(Message::make('error.noData', 'user'));
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

  // set output
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output data
  return Output::result($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
