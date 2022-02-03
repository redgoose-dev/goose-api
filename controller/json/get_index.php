<?php
namespace Core;
use Controller\Main;
use Exception;

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
  if ($name = $this->get->name ?? null)
  {
    $where .= ' and name LIKE \'%'.$name.'%\'';
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Main::checkAccessIndex($this, true);
  if ($token->data->srl ?? 0 && !$token->data->admin)
  {
    $where .= ' and user_srl='.(int)$token->data->srl;
  }

  // output
  $output = Main::index($this, (object)[
    'table' => 'json',
    'where' => $where,
    'json_field' => ['json'],
  ]);

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output
  return Output::result($output);
}
catch(Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
