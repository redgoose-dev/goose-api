<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * get json
 *
 * @var Goose $this
 */

try
{
  // set where
  $where = '';
  if ($name = Util::getParameter('name'))
  {
    $where .= ' and name LIKE \'%'.$name.'%\'';
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessIndex($this->model, true);
  $where .= (!$token->data->admin && $token->data->user_srl) ? ' and user_srl='.(int)$token->data->user_srl : '';

  // output
  $output = Controller\Main::index((object)[
    'model' => $this->model,
    'table' => 'json',
    'where' => $where,
    'json_field' => ['json'],
  ]);

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output
  Output::data($output);
}
catch(Exception $e)
{
  $this->model->disconnect();
  Error::data($e->getMessage(), $e->getCode());
}
