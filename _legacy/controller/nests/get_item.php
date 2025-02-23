<?php
namespace Core;
use Controller\Main;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * get nest
 *
 * @var Goose|Connect $this
 */

try
{
  $srl = $this->params['srl'] ?? null;
  $id = $this->params['id'] ?? null;

  // check params
  if (!($srl || $id))
  {
    throw new Exception(Message::make('noItems_or', 'srl', 'id'));
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Main::checkAccessItem($this, (object)[
    'table' => 'nests',
    'srl' => $srl,
    'id' => $id,
    'useStrict' => true,
  ]);

  // set output
  $output = Main::item($this, (object)[
    'table' => 'nests',
    'srl' => $srl,
    'id' => $id,
    'json_field' => ['json'],
  ]);

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output data
  return Output::result($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
