<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * delete file
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

  // check access
  $token = Controller\Main::checkAccessItem($this, (object)[
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
  if (isset($file->data->path) && $file->data->path && file_exists(__API_PATH__.'/'.$file->data->path))
  {
    unlink(__API_PATH__.'/'.$file->data->path);
  }

  // remove item
  $output = Controller\Main::delete($this, (object)[
    'table' => 'files',
    'srl' => $srl,
  ]);

  // set output
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output data
  return Output::data($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::data($e->getMessage(), $e->getCode());
}
