<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get categories
 *
 * url params
 * - @param int nest
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
	if ($nest = (int)$_GET['nest'])
	{
		$where .= ' and nest_srl='.$nest;
	}
	if ($name = $_GET['name'])
	{
		$where .= ' and name LIKE \'%'.$name.'%\'';
	}

	// check access
	$token = Controller::checkAccessIndex($model, true);
	$where .= (!$token->data->admin && $token->data->user_srl) ? ' and user_srl='.(int)$token->data->user_srl : '';

	// set output
	$output = Controller::index((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'categories',
		'where' => $where
	]);

	if (isset($_GET['ext_field']) && $output->data)
	{
		$output->data->index = \Controller\categories\Util::extendItems(
			$model,
			$token,
			$output->data->index,
			$nest
		);
	}

	// set token
	if ($token) $output->_token = $token->jwt;

	// output
	Output::data($output);
}
catch (Exception $e)
{
	if (isset($model)) $model->disconnect();
	Error::data($e->getMessage(), $e->getCode());
}
