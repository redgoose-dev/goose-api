<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * get checklist items
 *
 * @var Goose|Connect $this
 */

try
{
  // connect db
  $this->model->connect();

  // check access
  $token = Auth::checkAuthorization($this->model, 'user');

  // set where
  $where = '';
  if (isset($this->get->start) && isset($this->get->end))
  {
    $where .= ' and regdate between \''.$this->get->start.'\' and \''.$this->get->end.'\'';
  }
  if ($q = $this->get->q)
  {
    $where .= ' and content LIKE \'%'.$q.'%\'';
  }
  $where .= ' and user_srl='.(int)$token->data->user_srl;

  // set output
  $output = Controller\Main::index($this, (object)array_merge((array)$this->get, (array)[
    'table' => 'checklist',
    'where' => $where,
    'object' => false,
    'debug' => __API_DEBUG__,
  ]));

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
