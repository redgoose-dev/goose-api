<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get user
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

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');
  if (!$token->data->admin && ((int)$token->data->user_srl !== $srl))
  {
    throw new Exception(Message::make('error.access'), 401);
  }

  // set output
  $output = Controller::item((object)[
    'model' => $this->model,
    'table' => 'users',
    'srl' => $srl,
  ], function($result=null) {
    // delete password field
    if (!isset($result->data)) return $result;
    if (isset($result->data->password)) unset($result->data->password);
    return $result;
  });

  // set token
  if ($token->jwt) $output->_token = $token->jwt;

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
