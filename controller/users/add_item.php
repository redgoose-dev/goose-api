<?php
namespace Core;
use Exception, Controller;

if (!defined('__GOOSE__')) exit();

/**
 * add user
 *
 * @var Goose $this
 */

try
{
  // check post values
  Util::checkExistValue($_POST, [ 'name', 'email', 'password' ]);

  // confirm match password
  if ($_POST['password'] !== $_POST['password2'])
  {
    throw new Exception(Message::make('error.matchPassword'));
  }

  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'admin');

  // check email address
  $cnt = $this->model->getCount((object)[
    'table' => 'users',
    'where' => 'email="'.$_POST['email'].'"',
  ]);
  if (isset($cnt->data) && $cnt->data > 0)
  {
    throw new Exception(Message::make('error.existsValue', 'email address'));
  }

  // set output
  try
  {
    $output = Controller\Main::add((object)[
      'model' => $this->model,
      'table' => 'users',
      'data' => (object)[
        'srl' => null,
        'email' => $_POST['email'],
        'name' => $_POST['name'],
        'password' => Text::createPassword($_POST['password']),
        'admin' => !!$_POST['admin'] ? (int)$_POST['admin'] : 1,
        'regdate' => date('Y-m-d H:i:s'),
      ],
    ]);
  }
  catch(Exception $e)
  {
    throw new Exception(Message::make('error.failedAdd', 'user'));
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
