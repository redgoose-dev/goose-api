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
	// check srl
	if (!((int)$this->params['srl'] && $this->params['srl'] > 0))
	{
		throw new Exception('Not found srl', 204);
	}

	// set model
	$model = new Model();
	$model->connect();

	// check data
	$cnt = $model->getCount((object)[
		'table' => 'users',
		'where' => 'srl='.(int)$this->params['srl'],
	])->data;
	if (!$cnt) throw new Exception('No user data.', 204);

	// check authorization
	$token = null;
	$jwt = Token::get(__TOKEN__);
	if ((int)$jwt->data->user_srl === (int)$this->params['srl'])
	{
		$token = Auth::checkAuthorization($model, 'user'); // self
	}
	else
	{
		$token = Auth::checkAuthorization($model, 'admin'); // admin
	}

	// check email address
	if (!!$_POST['email'])
	{
		$cnt = $model->getCount((object)[
			'table' => 'users',
			'where' => 'email="'.$_POST['email'].'" and srl!='.(int)$this->params['srl'],
			'debug' => __DEBUG__
		]);
		if (isset($cnt->data) && $cnt->data > 0)
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
			'table' => 'users',
			'srl' => (int)$this->params['srl'],
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