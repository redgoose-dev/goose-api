<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * get user
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

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');
  if (!$token->data->admin && ((int)$token->data->srl !== $srl))
  {
    throw new Exception(Message::make('error.access'), 401);
  }

  // set output
  $output = Controller\Main::item($this, (object)[
    'table' => 'users',
    'srl' => $srl,
    'json_field' => ['json'],
  ], function(object $result) {
    if (!($result->data ?? false)) return $result;
    // delete password field
    if ($result->data->password ?? false) unset($result->data->password);
    return $result;
  });

  // set token
  if ($token->jwt) $output->_token = $token->jwt;

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
