<?php
namespace Core;
use Controller\Main, Controller\nests\UtilForNests;
use Exception;

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
  if ($app = $this->get->app ?? null)
  {
    $where .= (strtolower($app) === 'null') ? ' and app_srl IS NULL' : ' and app_srl='.$app;
  }
  if ($id = $this->get->id ?? null)
  {
    $where .= ' and id LIKE \''.$id.'\'';
  }
  if ($name = $this->get->name ?? null)
  {
    $where .= ' and name LIKE \'%'.$name.'%\'';
  }

  // check access
  $token = Main::checkAccessIndex($this, true);
  if (($token->data->admin ?? false) && isset($this->get->user))
  {
    $where .= ' and user_srl='.(int)$this->get->user;
  }
  else if (!($token->data->admin ?? false) && isset($token->data->srl))
  {
    $where .= ' and user_srl='.(int)$token->data->srl;
  }

  // output
  $output = Main::index($this, (object)[
    'table' => 'nests',
    'where' => $where,
    'json_field' => ['json'],
  ]);

  // set external items
  if ($output->data)
  {
    $ext_field = $this->get->ext_field ?? null;
    // get articles count
    if (Util::checkKeyInExtField('count_articles', $ext_field))
    {
      $output->data->index = UtilForNests::getCountArticles(
        $this,
        $output->data->index,
        $token
      );
    }
    // get app title
    if (Util::checkKeyInExtField('app_name', $ext_field))
    {
      $output->data->index = UtilForNests::getAppName(
        $this,
        $output->data->index
      );
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
