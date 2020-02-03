<?php
namespace Core;
use Exception, Controller;

if (!defined('__GOOSE__')) exit();

/**
 * edit user
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

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');
  if (!$token->data->admin && ((int)$token->data->user_srl !== $srl))
  {
    throw new Exception(Message::make('error.access'), 401);
  }

  // check data
  $cnt = $this->model->getCount((object)[
    'table' => 'users',
    'where' => 'srl='.$srl,
  ]);
  if (!$cnt->data)
  {
    throw new Exception(Message::make('error.noData', 'user'));
  }

  // check email address
  if (!!$_POST['email'])
  {
    $cnt = $this->model->getCount((object)[
      'table' => 'users',
      'where' => 'email="'.$_POST['email'].'" and srl!='.$srl,
      'debug' => __DEBUG__,
    ]);
    if (!!$cnt->data)
    {
      throw new Exception(Message::make('error.existsValue'));
    }
  }

  try
  {
    // set output
    $output = Controller\Main::edit((object)[
      'model' => $this->model,
      'table' => 'users',
      'srl' => $srl,
      'data' => [
        $_POST['email'] ? "email='$_POST[email]'" : '',
        $_POST['name'] ? "name='$_POST[name]'" : '',
        ($_POST['admin'] && $token->data->admin) ? "admin=".(int)$_POST['admin'] : '',
      ],
    ]);
  }
  catch(Exception $e)
  {
    throw new Exception(Message::make('error.failedEdit', 'user'));
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
