<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * decode token
 *
 * @var Goose $this
 */

try
{
  // set values
  $output = (object)[];

  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model);

  // get decode token
  $jwt = Token::get(__TOKEN__);

  // set output
  $output->code = 200;
  $output->data = $jwt->data;

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output
  Output::data($output);
}
catch(Exception $e)
{
  $this->model->disconnect();
  Error::data($e->getMessage(), $e->getCode());
}
