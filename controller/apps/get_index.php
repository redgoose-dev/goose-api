<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get apps
 *
 * @var Goose $this
 */

try
{
  // connect db
  $this->model->connect();

  // set where
  $where = '';
  if ($id = $_GET['id'])
  {
    $where .= ' and id LIKE \''.$id.'\'';
  }
  if ($name = $_GET['name'])
  {
    $where .= ' and name LIKE \'%'.$name.'%\'';
  }

  // check access
  $token = Controller::checkAccessIndex($this->model, true);
  $where .= (!$token->data->admin && $token->data->user_srl) ? ' and user_srl='.(int)$token->data->user_srl : '';

  // set output
  $output = Controller::index((object)[
    'model' => $this->model,
    'table' => 'apps',
    'where' => $where,
  ]);

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output
  Output::data($output);
}
catch (Exception $e)
{
  if (isset($model)) $model->disconnect();
  Error::data($e->getMessage(), $e->getCode());
}
