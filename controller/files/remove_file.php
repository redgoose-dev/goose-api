<?php
namespace Core;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * remove file
 *
 * @var Goose $this
 */

try
{
  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');

  // check value
  if (!$_POST['path'])
  {
    throw new Exception(Message::make('msg.notExist', 'path'));
  }

  // set path
  $path = __API_PATH__.'/'.$_POST['path'];

  // check exist file
  if (!file_exists($path))
  {
    throw new Exception(Message::make('msg.noFiles'));
  }

  // delete file
  unlink($path);

  // set output
  $output = (object)[];
  $output->code = 200;

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output
  Output::data($output);
}
catch (Exception $e)
{
  $this->model->disconnect();
  Error::data($e->getMessage(), $e->getCode());
}
