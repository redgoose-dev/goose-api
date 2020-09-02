<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * add user
 *
 * @var Goose|Connect $this
 */

try
{
  // check post values
  Util::checkExistValue($this->post, [ 'name', 'email', 'password' ]);

  // confirm match password
  if ($this->post->password !== $this->post->password2)
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
    'where' => 'email="'.$this->post->email.'"',
  ]);
  if (isset($cnt->data) && $cnt->data > 0)
  {
    throw new Exception(Message::make('error.existsValue', 'email address'));
  }

  // set output
  try
  {
    $output = Controller\Main::add($this, (object)[
      'table' => 'users',
      'data' => (object)[
        'srl' => null,
        'email' => $this->post->email,
        'name' => $this->post->name,
        'password' => Text::createPassword($this->post->password),
        'admin' => isset($this->post->admin) ? (int)$this->post->admin : 1,
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
  return Output::data($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::data($e->getMessage(), $e->getCode());
}
