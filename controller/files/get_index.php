<?php
namespace Core;
use Exception, Controller\Main;

if (!defined('__API_GOOSE__')) exit();

/**
 * get files
 *
 * @var Goose|Connect $this
 */

try
{
  // connect db
  $this->model->connect();

  // check access
  $token = Main::checkAccessIndex($this, true);

	// set where
	$where = '';
	if ($target = ($this->get->target ?? null))
	{
		$where .= ' and target_srl='.$target;
	}
	if ($name = ($this->get->name ?? null))
	{
		$where .= ' and name LIKE \'%'.$name.'%\'';
	}
	if ($type = ($this->get->type ?? null))
	{
		$where .= ' and type LIKE \'%'.$type.'%\'';
	}
  if ($module = ($this->get->module ?? null))
  {
    $where .= ' and module LIKE \''.$module.'\'';
  }
  if (isset($token->data->srl) && !$token->data->admin)
  {
    $where .= ' and user_srl='.(int)$token->data->srl;
  }

	// set output
	$output = Main::index($this, (object)[
		'table' => 'files',
		'where' => $where,
	]);

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
