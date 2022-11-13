<?php
namespace Core;
use Controller\Main, Exception;
use Controller\categories\UtilArticlesForCategories;
use Controller\categories\UtilJsonForCategories;
use Controller\categories\UtilForCategories;

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

  // set base values
  $module = UtilForCategories::$module[$this->get->module ?? null] ?? null;

  // set where
  $where = '';
  if ($module)
  {
    $where .= ' and module="'.$module.'"';
  }
  if ($target = (int)($this->get->target ?? 0))
  {
    $where .= ' and target_srl='.$target;
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

  // extend fields (count,all,none)
  if ($output->data && ($this->get->ext_field ?? false))
  {
    switch ($module)
    {
      case UtilForCategories::$module['article']:
        $output->data->index = UtilArticlesForCategories::extendItems($this, $token, $output->data->index, $target);
        break;
      case UtilForCategories::$module['json']:
        $output->data->index = UtilJsonForCategories::extendItems($this, $token, $output->data->index);
        break;
    }
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
