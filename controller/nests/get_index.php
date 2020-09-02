<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * get nests
 *
 * @var Goose|Connect $this
 */

try
{
  // connect db
  $this->model->connect();

  // set where
  $where = '';
  if ($app = $this->get->app)
  {
    $where .= (strtolower($app) === 'null') ? ' and app_srl IS NULL' : ' and app_srl='.$app;
  }
  if ($id = $this->get->id)
  {
    $where .= ' and id LIKE \''.$id.'\'';
  }
  if ($name = $this->get->name)
  {
    $where .= ' and name LIKE \'%'.$name.'%\'';
  }

  // check access
  $token = Controller\Main::checkAccessIndex($this, true);
  if ($token->data->admin && isset($this->get->user))
  {
    $where .= ' and user_srl='.(int)$this->get->user;
  }
  else if (isset($token->data->user_srl) && !$token->data->admin)
  {
    $where .= ' and user_srl='.(int)$token->data->user_srl;
  }

  // output
  $output = Controller\Main::index($this, (object)[
    'table' => 'nests',
    'where' => $where,
    'json_field' => ['json'],
  ]);

  // get articles count
  if ($output->data && Util::checkKeyInExtField('count_articles', $this->get->ext_field))
  {
    $output->data->index = Controller\nests\UtilForNests::getCountArticles(
      $this,
      $output->data->index,
      $token
    );
  }
  // get app title
  if ($output->data && Util::checkKeyInExtField('app_name', $this->get->ext_field))
  {
    $output->data->index = Controller\nests\UtilForNests::getAppName(
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
