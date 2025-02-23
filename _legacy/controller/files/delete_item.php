<?php
namespace Core;
use Exception, Controller\Main;

if (!defined('__API_GOOSE__')) exit();

/**
 * delete file
 *
 * @var Goose|Connect $this
 */

try
{
  // check upload directories
  Util::checkDirectories();

  // check and set srl
  if (($srl = (int)($this->params['srl'] ?? 0)) <= 0)
  {
    throw new Exception(Message::make('error.notFound', 'srl'));
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Main::checkAccessItem($this, (object)[
    'table' => 'files',
    'srl' => $srl,
  ]);

  /**
   * remove file
   */
  // get item
  $file = $this->model->getItem((object)[
    'table' => 'files',
    'field' => 'path',
    'where' => 'srl='.$srl,
  ]);

  // check exist file
  if (($file->data->path ?? false) && file_exists(__API_PATH__.'/'.$file->data->path))
  {
    unlink(__API_PATH__.'/'.$file->data->path);
  }

  // remove data
  $output = Main::delete($this, (object)[
    'table' => 'files',
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
  if ($this->model ?? false) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
