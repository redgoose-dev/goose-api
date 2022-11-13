<?php
namespace Core;
use Exception, Controller\Main;

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
  if ($q = $this->get->q ?? null)
  {
    $where .= ' and content LIKE \'%'.$q.'%\'';
  }
  $where .= ' and user_srl='.(int)$token->data->srl;

  // set output
  $output = Main::index(
    $this,
    (object)array_merge(
      (array)$this->get,
      [
        'table' => 'checklist',
        'where' => $where,
        'object' => false,
      ]
    )
  );

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output
  return Output::result($output);
}
catch (Exception $e)
{
  if ($this->model ?? false) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
