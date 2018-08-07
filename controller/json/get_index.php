<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get json
 *
 * url params
 * - @param string name
 *
 * @var Goose $this
 */

try
{
	// set where
	$where = '';
	if ($name = Util::getParameter('name'))
	{
		$where .= ' and name LIKE \'%'.$name.'%\'';
	}

	// set model
	$model = new Model();
	$model->connect();

	// check access
	$token = Controller::checkAccessIndex($model, true);
	$where .= (!$token->data->admin && $token->data->user_srl) ? ' and user_srl='.(int)$token->data->user_srl : '';

	// output
	$output = Controller::index((object)[
		'goose' => $this,
		'table' => 'json',
		'where' => $where,
		'json_field' => ['json'],
	]);

	// set token
	if ($token) $output->_token = $token->jwt;

	// output
	Output::data($output);
}
catch(Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
