<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * get users
 *
 * @var Goose $this
 */

try
{
  // connect db
  $this->model->connect();

  // set where
  $where = '';
  if ($email = $_GET['email'])
  {
    $where .= ' and email LIKE \''.$email.'\'';
  }
  if ($name = $_GET['name'])
  {
    $where .= ' and name LIKE \'%'.$name.'%\'';
  }
  if ($admin = $_GET['admin'])
  {
    $where .= ' and admin='.(int)$admin;
  }

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');
  if (!$token->data->admin) $where .= ' and srl='.$token->data->user_srl;

  // output
  $output = Controller\Main::index((object)[
    'model' => $this->model,
    'auth' => true,
    'table' => 'users',
    'where' => $where,
  ], function($result=null) {
    if (!isset($result->data)) return $result;
    foreach ($result->data as $k=>$o)
    {
      // remove password field
      if (isset($result->data[$k]->password))
      {
        unset($result->data[$k]->password);
      }
    }
    return $result;
  });

  // set token
  if ($token->jwt) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output
  Output::data($output);
}
catch (Exception $e)
{
  $this->model->disconnect();
  Error::data($e->getMessage(), $e->getCode());
}
