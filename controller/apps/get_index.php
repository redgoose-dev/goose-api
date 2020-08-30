<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

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
  if ($user_srl = $_GET['user'])
  {
    $where .= ' and user_srl='.(int)$user_srl;
  }

  // check access
  $token = Controller\Main::checkAccessIndex($this->model, true);
  if (!$token->data->admin && $token->data->user_srl)
  {
    $where .= ' and user_srl='.(int)$token->data->user_srl;
  }

  // set output
  $output = Controller\Main::index((object)[
    'model' => $this->model,
    'table' => 'apps',
    'where' => $where,
  ]);

  if ($output->data && Util::checkKeyInExtField('count_nests'))
  {
    $output->data->index = Controller\apps\UtilForApps::getCountNests(
      $this->model,
      $output->data->index
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
  if (isset($model)) $model->disconnect();
  Error::data($e->getMessage(), $e->getCode());
}
