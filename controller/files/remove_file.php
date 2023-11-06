<?php
namespace Core;
use Exception;
use Controller\files\UtilForFiles;

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
  Util::checkExistValue($this->post, [ 'dir', 'path' ]);

  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');

  // set path
  $dir = $this->post->dir ?? 'user';
  $path = $this->post->path;
  $absolutePath = UtilForFiles::$uploadFull.$dir.'/'.$path;

  // check exist file
  if (!file_exists($absolutePath))
  {
    throw new Exception(Message::make('msg.noFiles'), 204);
  }

  // delete file
  unlink($absolutePath);

  // update map file
  $json = UtilForFiles::getAssetsMapFiles($dir);
  if (!$json) $json = UtilForFiles::createAssetsMapFile($dir);
  if ($json->{$path} ?? false)
  {
    unset($json->{$path});
    UtilForFiles::writeAssetsMapFile($json, $dir);
  }

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
  if ($this->model ?? false) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
