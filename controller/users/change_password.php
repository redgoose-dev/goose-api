<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * change password
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

	// check post values
	Util::checkExistValue($_POST, [ 'password', 'new_password', 'confirm_password' ]);

	// check new_password and confirm_password
	if ($_POST['new_password'] !== $_POST['confirm_password'])
	{
		throw new Exception('`new_password` and `confirm_password` are different.', 204);
	}

	// set model
	$model = new Model();
	$model->connect();

	// check authorization
	$token = Auth::checkAuthorization($model, 'user');

	try
	{
		// check password
		Auth::login((object)[
			'user_srl' => (int)$this->params['srl'],
			'password' => $_POST['password']
		]);

		// set output
		$output = Controller::edit((object)[
			'goose' => $this,
			'model' => $model,
			'table' => 'users',
			'srl' => (int)$this->params['srl'],
			'data' => [ "password='".password_hash($_POST['new_password'], PASSWORD_DEFAULT)."'" ],
		]);
	}
	catch(Exception $e)
	{
		throw new Exception('Failed change password.', 204);
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