<?php
namespace Core;
use Exception, Controller, Controller\Main;

if (!defined('__API_GOOSE__')) exit();

/**
 * get users
 *
 * @var Goose|Connect $this
 */

try
{
  // connect db
  $this->model->connect();

  // set where
  $where = '';
  if ($email = $this->get->email ?? false)
  {
    $where .= ' and email LIKE \''.$email.'\'';
  }
  if ($name = $this->get->name ?? false)
  {
    $where .= ' and name LIKE \'%'.$name.'%\'';
  }
  if (isset($this->get->admin))
  {
    $admin = (int)$this->get->admin;
    $where .= ' and admin='.$admin;
  }

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');
  if (!$token->data->admin) $where .= ' and srl='.$token->data->srl;

  // output
  $output = Main::index($this, (object)[
    'auth' => true,
    'table' => 'users',
    'where' => $where,
    'json_field' => ['json'],
  ], function($result=null) {
    if (!isset($result->data)) return $result;
    foreach ($result->data as $k=>$o)
    {
      // remove password field
      if (isset($result->data[$k]->password)) unset($result->data[$k]->password);
    }
    return $result;
  });

  // set token
  if ($token->jwt) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output
  return Output::result($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
