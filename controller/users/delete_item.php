<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * delete user
 *
 * @var Goose $this
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
    throw new Exception(Message::make('error.noData'));
  }

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'admin');
  if (!$token->data->admin && ((int)$token->data->user_srl !== $srl))
  {
    throw new Exception(Message::make('error.access'), 401);
  }

  // remove item
  $output = Controller\Main::delete((object)[
    'model' => $this->model,
    'table' => 'users',
    'srl' => $srl,
  ]);

  // set output
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output data
  Output::data($output);
}
catch (Exception $e)
{
  $this->model->disconnect();
  Error::data($e->getMessage(), $e->getCode());
}
