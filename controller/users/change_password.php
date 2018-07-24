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
		throw new Exception('Not found srl', 500);
	}

	// check post values
	Util::checkExistValue($_POST, [ 'pw', 'new_pw', 'confirm_pw' ]);

	// check new_pw and confirm_pw
	if ($_POST['new_pw'] !== $_POST['confirm_pw'])
	{
		throw new Exception('`new_pw` and `confirm_pw` are different.', 204);
	}

	// set model
	$model = new Model();
	$model->connect();

	// check authorization
	$token = Auth::checkAuthorization(0, $model);

	try
	{
		// check password
		Auth::login((object)[
			'user_srl' => (int)$this->params['srl'],
			'password' => $_POST['pw']
		]);

		// set output
		$output = Controller::edit((object)[
			'goose' => $this,
			'model' => $model,
			'table' => 'user',
			'srl' => (int)$this->params['srl'],
			'data' => [ "pw='".password_hash($_POST['new_pw'], PASSWORD_DEFAULT)."'" ],
		]);
	}
	catch(Exception $e)
	{
		throw new Exception('Failed change password.', 204);
	}

	// set token
	if ($token) $output->_token = $token;

	// disconnect db
	$model->disconnect();

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}