<?php
namespace Core;
use Controller\Main;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit category
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

  // connect db
  $this->model->connect();

  // check access
  $token = Main::checkAccessItem($this, (object)[
    'table' => 'categories',
    'srl' => $srl,
  ]);

  // check exist nest
  if (isset($this->post->nest_srl))
  {
    $cnt = $this->model->getCount((object)[
      'table' => 'nests',
      'where' => 'srl='.(int)$this->post->nest_srl,
    ])->data;
    if ($cnt <= 0)
    {
      throw new Exception(Message::make('error.noData', 'nest'));
    }
  }

  // set data
  $data = [];
  if (isset($this->post->nest_srl)) $data[] = 'nest_srl='.(int)$this->post->nest_srl;
  if (isset($this->post->name)) $data[] = "name='{$this->post->name}'";
  if (count($data) <= 0)
  {
    throw new Exception(Message::make('error.noEditData'));
  }

  // edit data
  $output = Main::edit($this, (object)[
    'table' => 'categories',
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
