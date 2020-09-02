<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * get json
 *
 * @var Goose|Connect $this
 */

try
{
  // set where
  $where = '';
  if ($name = $this->get->name)
  {
    $where .= ' and name LIKE \'%'.$name.'%\'';
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessIndex($this, true);
  if (isset($token->data->user_srl) && !$token->data->admin)
  {
    $where .= ' and user_srl='.(int)$token->data->user_srl;
  }

  // output
  $output = Controller\Main::index($this, (object)[
    'table' => 'json',
    'where' => $where,
    'json_field' => ['json'],
  ]);

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output
  return Output::data($output);
}
catch(Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::data($e->getMessage(), $e->getCode());
}
