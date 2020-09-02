<?php
namespace Core;
use Exception, Controller;

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
  $token = Controller\Main::checkAccessIndex($this, true);

	// set where
	$where = '';
	if ($target = $this->get->target)
	{
		$where .= ' and target_srl='.$target;
	}
	if ($name = $this->get->name)
	{
		$where .= ' and name LIKE \'%'.$name.'%\'';
	}
	if ($type = $this->get->type)
	{
		$where .= ' and type LIKE \'%'.$type.'%\'';
	}
  if ($module = $this->get->module)
  {
    $where .= ' and module LIKE \''.$module.'\'';
  }
  if (isset($token->data->user_srl) && !$token->data->admin)
  {
    $where .= ' and user_srl='.(int)$token->data->user_srl;
  }

	// set output
	$output = Controller\Main::index($this, (object)[
		'table' => 'files',
		'where' => $where,
	]);

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
