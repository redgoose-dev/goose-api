<?php
namespace Core;
use Controller\Main, Controller\categories\UtilForCategories;
use Exception;

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
  if ($nest = (int)($this->get->nest ?? 0))
  {
    $where .= ' and nest_srl='.$nest;
  }
  if ($name = ($this->get->name ?? null))
  {
    $where .= ' and name LIKE \'%'.$name.'%\'';
  }

  // check access
  $token = Main::checkAccessIndex($this, true);
  $where .= (!($token->data->admin ?? false) && ($token->data->srl ?? false)) ? ' and user_srl='.(int)$token->data->srl : '';

  // set output
  $output = Main::index($this, (object)[
    'table' => 'categories',
    'where' => $where,
  ]);

  // extend fields (count_article,item_all,none)
  if ($output->data && ($this->get->ext_field ?? false))
  {
    $output->data->index = UtilForCategories::extendItems(
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
  return Output::result($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
