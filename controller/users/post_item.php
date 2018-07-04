<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * add user
 *
 * data params
 * - @param string email
 * - @param string name
 * - @param string pw
 * - @param string pw2
 * - @param int level
 *
 * @var Goose $this
 */

try
{
	// check authorization
	Auth::checkAuthorization($this->level->admin);

	// set values
	$output = (object)[];

	// check post values
	Util::checkExistValue($_POST, [ 'name', 'email', 'pw', 'level' ]);

	// confirm match password
	if ($_POST['pw'] !== $_POST['pw2'])
	{
		throw new Exception('Passwords must match', 500);
	}

	// set model and connect db
	$model = new Model();
	$model->connect();

	// check email address
	$cnt = $model->getCount((object)[
		'table' => 'user',
		'where' => 'email="'.$_POST['email'].'"',
		'debug' => __DEBUG__
	]);
	if (isset($cnt->data) && $cnt->data > 0)
	{
		throw new Exception('The email address already exists.', 500);
	}

	// add data
	$result = $model->add((object)[
		'table' => 'user',
		'data' => (object)[
			'srl' => null,
			'email' => $_POST['email'],
			'name' => $_POST['name'],
			'pw' => password_hash($_POST['pw'], PASSWORD_DEFAULT),
			'level' => $_POST['level'] ? $_POST['level'] : 0,
			'regdate' => date('YmdHis')
		],
		'debug' => __DEBUG__
	]);

	// set output
	$output->code = 200;
	$output->query = $result->query;

	// disconnect db
	$model->disconnect();

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
