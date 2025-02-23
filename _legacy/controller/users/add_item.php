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

  // check and set json
  $json = ($this->post->json ?? false) ? Util::testJsonData($this->post->json) : null;

  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'admin');

  // test email address
  Text::checkEmail($this->post->email);

  // check email address
  $cnt = $this->model->getCount((object)[
    'table' => 'users',
    'where' => 'email="'.$this->post->email.'"',
  ])->data;
  if ($cnt > 0)
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
        'admin' => (int)($this->post->admin ?? 1),
        'json' => $json,
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
  return Output::result($output);
}
catch (Exception $e)
{
  if ($this->model ?? false) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
