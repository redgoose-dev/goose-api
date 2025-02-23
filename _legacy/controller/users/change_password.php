<?php
namespace Core;
use Exception, Controller\Main;

if (!defined('__API_GOOSE__')) exit();

/**
 * change password
 *
 * @var Goose|Connect $this
 */

try
{
  // check and set srl
  $srl = (int)($this->params['srl'] ?? 0);
  if ($srl <= 0)
  {
    throw new Exception(Message::make('error.notFound', 'srl'), 404);
  }

  // check post values
  Util::checkExistValue($this->post, [ 'password', 'new_password', 'confirm_password' ]);

  // check new_password and confirm_password
  if ($this->post->new_password !== $this->post->confirm_password)
  {
    throw new Exception(Message::make('error.different', 'new_password', 'confirm_password'));
  }

  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');

  try
  {
    // check password
    $user = Auth::login((object)[
      'model' => $this->model,
      'user_srl' => (int)$this->params['srl'],
      'password' => $this->post->password,
    ]);
    // set output
    $output = Main::edit($this, (object)[
      'table' => 'users',
      'srl' => $user->srl,
      'data' => [
        "password='".Text::createPassword($this->post->new_password)."'",
      ],
    ]);
  }
  catch(Exception $e)
  {
    throw new Exception($e->getMessage(), $e->getCode());
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
  $message = __API_DEBUG__ ? $e->getMessage() : Message::make('error.failedChange', 'password');
  return Error::result($message);
}
