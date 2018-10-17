<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get drafts
 *
 * @var Goose $this
 */

try
{
	// set model
	$model = new Model();
	$model->connect();

	// check access
	$token = Controller::checkAccessIndex($model, true);
	$where = (!$token->data->admin && $token->data->user_srl) ? 'user_srl='.(int)$token->data->user_srl : '';

	// set output
	$output = Controller::index((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'drafts',
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
	if (isset($model)) $model->disconnect();
	Error::data($e->getMessage(), $e->getCode());
}
