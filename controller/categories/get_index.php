<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get categories
 *
 * @var Goose $this
 */

try
{
  // connect db
  $this->model->connect();

  // set where
  $where = '';
  if ($nest = (int)$_GET['nest'])
  {
    $where .= ' and nest_srl='.$nest;
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
    'table' => 'categories',
    'where' => $where,
  ]);

  // extend fields
  if ($output->data && isset($_GET['ext_field']))
  {
    $output->data->index = \Controller\categories\UtilForCategories::extendItems(
      $this->model,
      $token,
      $output->data->index,
      $nest
    );
  }

  // set token
  if ($token) $output->_token = $token->jwt;

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
