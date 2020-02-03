<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * change password
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

  // check post values
  Util::checkExistValue($_POST, [ 'password', 'new_password', 'confirm_password' ]);

  // check new_password and confirm_password
  if ($_POST['new_password'] !== $_POST['confirm_password'])
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
      'password' => $_POST['password']
    ]);

    // set output
    $output = Controller::edit((object)[
      'model' => $this->model,
      'table' => 'users',
      'srl' => (int)$this->params['srl'],
      'data' => [ "password='".Text::createPassword($_POST['new_password'])."'" ],
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
  Output::data($output);
}
catch (Exception $e)
{
  $this->model->disconnect();
  $message = __DEBUG__ ? $e->getMessage() : Message::make('error.failedChange', 'password');
  Error::data($message, $e->getCode());
}
