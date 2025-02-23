<?php
namespace Core;
use Exception, Controller\Main;
use Controller\files\UtilForFiles;

if (!defined('__API_GOOSE__')) exit();

/**
 * delete json
 *
 * @var Goose|Connect $this
 */

try
{
  // check and set srl
  if (($srl = (int)($this->params['srl'] ?? 0)) <= 0)
  {
    throw new Exception(Message::make('error.notFound', 'srl'), 204);
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Main::checkAccessItem($this, (object)[
    'table' => 'json',
    'srl' => $srl,
  ]);

  // remove item
  $output = Main::delete($this, (object)[
    'table' => 'json',
    'srl' => $srl,
  ]);

  // remove files
  UtilForFiles::removeAttachFiles($this, $srl, 'json');

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
