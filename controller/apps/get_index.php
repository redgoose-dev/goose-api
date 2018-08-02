<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get apps
 *
 * url params
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
	if ($id = $_GET['id'])
	{
		$where .= ' and id LIKE \''.$id.'\'';
	}
	if ($name = $_GET['name'])
	{
		$where .= ' and name LIKE \'%'.$name.'%\'';
	}

	// check access
	if ($_GET['strict'])
	{
		$token = Auth::checkAuthorization($model, 'user');
		$where .= ($token->data->admin) ? '' : ' and user_srl='.(int)$token->data->user_srl;
	}
	else
	{
		$token = Auth::checkAuthorization($model);
	}

	// set output
	$output = Controller::index((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'apps',
		'where' => $where,
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
