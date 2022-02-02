<?php
namespace Core;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * remove file
 *
 * @var Goose|Connect $this
 */

try
{
  // check upload directories
  Util::checkDirectories();

  // check post values
  Util::checkExistValue($this->post, [ 'path' ]);

  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');

  // set path
  $path = __API_PATH__.'/'.$this->post->path;

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
  return Output::result($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
