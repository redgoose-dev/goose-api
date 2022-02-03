<?php
namespace Core;
use Controller\Main;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit json
 *
 * @var Goose|Connect $this
 */

try
{
  // check and set srl
  if (($srl = (int)($this->params['srl'] ?? 0)) <= 0)
  {
    throw new Exception(Message::make('error.notFound', 'srl'));
  }

  // check post values
  Util::checkExistValue($this->post, [ 'name', 'json' ]);

  // set value
  $json = isset($this->post->json) ? Util::testJsonData($this->post->json) : null;

  // connect db
  $this->model->connect();

  // check access
  $token = Main::checkAccessItem($this, (object)[
    'table' => 'json',
    'srl' => $srl,
  ]);

  // set data
  $data = [];
  if (isset($this->post->name)) $data[] = "name='{$this->post->name}'";
  if (isset($this->post->description)) $data[] = "description='{$this->post->description}'";
  if ($json) $data[] = "json='$json'";
  if (count($data) <= 0)
  {
    throw new Exception(Message::make('error.noEditData'));
  }

  // set output
  $output = Main::edit($this, (object)[
    'table' => 'json',
    'srl' => $srl,
    'data' => $data,
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
