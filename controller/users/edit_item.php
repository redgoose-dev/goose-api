<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * edit user
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

	// check data
	$cnt = $model->getCount((object)[
		'table' => $tableName,
		'where' => 'srl='.$srl,
	]);
	if (!$cnt->data) throw new Exception('No user data.', 204);

	// check email address
	if (!!$_POST['email'])
	{
		$cnt = $model->getCount((object)[
			'table' => $tableName,
			'where' => 'email="'.$_POST['email'].'" and srl!='.$srl,
			'debug' => __DEBUG__
		]);
		if (!!$cnt->data)
		{
			throw new Exception('The email address already exists.', 204);
		}
	}

	try
	{
		// set output
		$output = Controller::edit((object)[
			'goose' => $this,
			'model' => $model,
			'table' => $tableName,
			'srl' => $srl,
			'data' => [
				$_POST['email'] ? "email='$_POST[email]'" : '',
				$_POST['name'] ? "name='$_POST[name]'" : '',
				($_POST['admin'] && $jwt->data->admin) ? "admin=".(int)$_POST['admin'] : '',
			],
		]);
	}
	catch(Exception $e)
	{
		throw new Exception('Failed edit user', 204);
	}

	// set token
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