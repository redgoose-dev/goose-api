<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * get categories
 *
 * @var Goose|Connect $this
 */

try
{
  // connect db
  $this->model->connect();

  // set where
  $where = '';
  if ($nest = (int)$this->get->nest)
  {
    $where .= ' and nest_srl='.$nest;
  }
  if ($name = $this->get->name)
  {
    $where .= ' and name LIKE \'%'.$name.'%\'';
  }

  // check access
  $token = Controller\Main::checkAccessIndex($this, true);
  $where .= (!$token->data->admin && $token->data->user_srl) ? ' and user_srl='.(int)$token->data->user_srl : '';

  // set output
  $output = Controller\Main::index($this, (object)[
    'table' => 'categories',
    'where' => $where,
  ]);

  // extend fields (count_article,item_all,none)
  if ($output->data && isset($this->get->ext_field))
  {
    $output->data->index = Controller\categories\UtilForCategories::extendItems(
      $this,
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
  return Output::data($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::data($e->getMessage(), $e->getCode());
}
