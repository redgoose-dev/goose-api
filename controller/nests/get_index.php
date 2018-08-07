<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get nests
 *
 * url params
 * - @param int app
 * - @param string id
 * - @param string name
 *
 * @var Goose $this
 */

try
{
	// set model
	$model = new Model();
	$model->connect();

	// set where
	$where = '';
	if ($app = $_GET['app'])
	{
		$where .= ($app === 'null' || $app === 'NULL') ? ' and app_srl IS NULL' : ' and app_srl='.$app;
	}
	if ($id = $_GET['id'])
	{
		$where .= ' and id LIKE \''.$id.'\'';
	}
	if ($name = $_GET['name'])
	{
		$where .= ' and name LIKE \'%'.$name.'%\'';
	}

	// check access
	$token = Controller::checkAccessIndex($model, true);
	$where .= (!$token->data->admin && $token->data->user_srl) ? ' and user_srl='.(int)$token->data->user_srl : '';

	// output
	$output = Controller::index((object)[
		'goose' => $this,
		'table' => 'nests',
		'where' => $where,
		'json_field' => ['json']
	]);

	// set token
	if ($token) $output->_token = $token->jwt;

	// output
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
