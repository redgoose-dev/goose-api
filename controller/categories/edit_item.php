<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * edit category
 *
 * @var Goose $this
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
  $token = Controller::checkAccessItem((object)[
    'model' => $this->model,
    'table' => 'categories',
    'srl' => $srl,
  ]);

  // check exist nest
  if (isset($_POST['nest_srl']))
  {
    $cnt = $this->model->getCount((object)[
      'table' => 'nests',
      'where' => 'srl='.(int)$_POST['nest_srl'],
    ]);
    if (!$cnt->data)
    {
      throw new Exception(Message::make('error.noData', 'nest'));
    }
  }

  // set output
  try
  {
    $output = Controller::edit((object)[
      'model' => $this->model,
      'table' => 'categories',
      'srl' => $srl,
      'data' => [
        isset($_POST['nest_srl']) ? 'nest_srl='.(int)$_POST['nest_srl'] : '',
        isset($_POST['name']) ? "name='$_POST[name]'" : '',
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
  Output::data($output);
}
catch (Exception $e)
{
  $this->model->disconnect();
  Error::data($e->getMessage(), $e->getCode());
}
