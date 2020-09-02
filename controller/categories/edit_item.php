<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit category
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
    'table' => 'categories',
    'srl' => $srl,
  ]);

  // check exist nest
  if (isset($this->post->nest_srl))
  {
    $cnt = $this->model->getCount((object)[
      'table' => 'nests',
      'where' => 'srl='.(int)$this->post->nest_srl,
    ]);
    if (!$cnt->data)
    {
      throw new Exception(Message::make('error.noData', 'nest'));
    }
  }

  // set output
  try
  {
    $output = Controller\Main::edit($this, (object)[
      'table' => 'categories',
      'srl' => $srl,
      'data' => [
        isset($this->post->nest_srl) ? 'nest_srl='.(int)$this->post->nest_srl : '',
        isset($this->post->name) ? "name='{$this->post->name}'" : '',
      ],
    ]);
  }
  catch(Exception $e)
  {
    throw new Exception(Message::make('error.failedEdit', 'category'));
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
