<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * delete user
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

	// check data
	$cnt = $model->getCount((object)[
		'table' => $tableName,
		'where' => 'srl='.$srl,
	]);
	if (!$cnt->data) throw new Exception('No user data.', 204);

	// check authorization
	$token = Auth::checkAuthorization($model, 'user');
	if (!$token->data->admin && ((int)$token->data->user_srl !== $srl))
	{
		throw new Exception('You can not access.', 401);
	}

	// remove item
	$output = Controller::delete((object)[
		'goose' => $this,
		'model' => $model,
		'table' => $tableName,
		'srl' => $srl,
	]);

	// set output
	if ($token) $output->_token = $token->jwt;

	// disconnect db
	$model->disconnect();

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
