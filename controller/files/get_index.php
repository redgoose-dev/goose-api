<?php
namespace Core;
use Exception, Controller;

if (!defined('__GOOSE__')) exit();

/**
 * get files
 *
 * @var Goose $this
 */

try
{
  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessIndex($this->model, true);

	// set where
	$where = '';
	if ($target = $_GET['target'])
	{
		$where .= ' and target_srl='.$target;
	}
	if ($name = $_GET['name'])
	{
		$where .= ' and name LIKE \'%'.$name.'%\'';
	}
	if ($type = $_GET['type'])
	{
		$where .= ' and type LIKE \'%'.$type.'%\'';
	}
  if ($module = $_GET['module'])
  {
    $where .= ' and module LIKE \''.$module.'\'';
  }
  if (!$token->data->admin)
  {
    $where .= isset($token->data->user_srl) ? ' and user_srl='.(int)$token->data->user_srl : '';
  }

	// set output
	$output = Controller\Main::index((object)[
		'model' => $this->model,
		'table' => 'files',
		'where' => $where,
	]);

	// set token
	if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

	// output
	Output::data($output);
}
catch (Exception $e)
{
  $this->model->disconnect();
	Error::data($e->getMessage(), $e->getCode());
}
