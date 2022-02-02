<?php
namespace Core;
use Controller\Main;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * add category
 *
 * @var Goose|Connect $this
 */

try
{
  // check post values
  Util::checkExistValue($this->post, [ 'nest_srl', 'name' ]);

  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');

  // check exist nest
  $cnt = $this->model->getCount((object)[
    'table' => 'nests',
    'where' => 'srl='.(int)$this->post->nest_srl,
  ])->data;
  if ($cnt <= 0)
  {
    throw new Exception(Message::make('error.noData', 'nest'));
  }

  // get max turn
  $max = $this->model->getMax((object)[
    'table' => 'categories',
    'field' => 'turn',
    'where' => 'nest_srl='.(int)$this->post->nest_srl,
  ])->data;

  // set output
  try
  {
    $output = Main::add($this, (object)[
      'table' => 'categories',
      'data' => (object)[
        'srl' => null,
        'nest_srl' => (int)$this->post->nest_srl,
        'user_srl' => (int)$token->data->srl,
        'turn' => $max + 1,
        'name' => trim($this->post->name ?? ''),
        'regdate' => date('Y-m-d H:i:s'),
      ],
    ]);
  }
  catch(Exception $e)
  {
    throw new Exception(Message::make('error.failedAdd', 'category'));
  }

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
