<?php
namespace Core;
use Exception, Controller;

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
  ]);
  if (!$cnt->data)
  {
    throw new Exception(Message::make('error.noData', 'nest'));
  }

  // get max turn
  $max = $this->model->getMax((object)[
    'table' => 'categories',
    'field' => 'turn',
    'where' => 'nest_srl='.(int)$this->post->nest_srl,
  ]);

  // set output
  try
  {
    $output = Controller\Main::add($this, (object)[
      'table' => 'categories',
      'data' => (object)[
        'srl' => null,
        'nest_srl' => $this->post->nest_srl,
        'user_srl' => (int)$token->data->user_srl,
        'turn' => isset($max->data) ? $max->data + 1 : 1,
        'name' => $this->post->name,
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
  return Output::data($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::data($e->getMessage(), $e->getCode());
}
