<?php
namespace Core;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * decode token
 *
 * @var Goose|Connect $this
 */

try
{
  // set values
  $output = (object)[];

  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model, '');

  // get decode token
  $jwt = Token::get(__API_TOKEN__);

  // set output
  $output->code = 200;
  $output->data = $jwt->data;

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output
  return Output::result($output);
}
catch(Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
