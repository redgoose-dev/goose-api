<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get user
 *
 * @var Goose $this
 */

try
{
	$tableName = 'users';
	$srl = (int)$this->params['srl'];

	// check srl
	if (!($srl && $srl > 0))
	{
		throw new Exception('Not found srl', 204);
	}

	// set model
	$model = new Model();
	$model->connect();

	// check authorization
	$token = Auth::checkAuthorization($model, 'user');
	if (!$token->data->admin && ((int)$token->data->user_srl !== $srl))
	{
		throw new Exception('You can not access.', 401);
	}

	// set output
	$output = Controller::item((object)[
		'goose' => $this,
		'model' => $model,
		'table' => $tableName,
		'srl' => $srl,
	], function($result=null) {
		// delete pw field
		if (!isset($result->data)) return $result;
		if (isset($result->data->pw)) unset($result->data->pw);
		return $result;
	});

	// set token
	if ($token->jwt) $output->_token = $token->jwt;

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
