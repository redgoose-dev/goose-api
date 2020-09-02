<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * get apps
 *
 * @var Goose|Connect $this
 */

try
{
  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessIndex($this, true);

  // set where
  $where = '';
  if ($id = $this->get->id)
  {
    $where .= ' and id LIKE \''.$id.'\'';
  }
  if ($name = $this->get->name)
  {
    $where .= ' and name LIKE \'%'.$name.'%\'';
  }
  if (!$token->data->admin && $token->data->user_srl)
  {
    $where .= ' and user_srl='.(int)$token->data->user_srl;
  }
  else if ($user_srl = $this->get->user)
  {
    $where .= ' and user_srl='.(int)$user_srl;
  }

  // set output
  $output = Controller\Main::index($this, (object)array_merge((array)$this->get, (array)[
    'table' => 'apps',
    'where' => $where,
    'object' => false,
    'debug' => __API_DEBUG__,
  ]));

  if ($output->data && Util::checkKeyInExtField('count_nests', $this->get->ext_field))
  {
    $output->data->index = Controller\apps\UtilForApps::getCountNests(
      $this,
      $output->data->index
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
