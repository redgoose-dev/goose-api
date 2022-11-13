<?php
namespace Core;
use Exception, Controller\Main;
use Controller\json\UtilForJson;

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
  if ($category = ($this->get->category ?? null))
  {
    $where .= (strtolower($category) === 'null') ? ' and category_srl IS NULL' : ' and category_srl='.$category;
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

  if ($output->data)
  {
    $ext_field = $this->get->ext_field ?? null;
    // get category name
    if (Util::checkKeyInExtField('category_name', $ext_field))
    {
      $output->data->index = UtilForJson::extendCategoryNameInItems($this, $output->data->index);
    }
  }

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output
  return Output::result($output);
}
catch(Exception $e)
{
  if ($this->model ?? false) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
