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

// TODO: 레벨에 의한 토큰검사, 관리자 수준의 권한이 이상이 되면 사용자 추가 가능

try
{
	$model = new Model();
	$output = (object)[];

	// check post values
	Util::checkExistValue($_POST, [ 'name', 'email', 'pw', 'level' ]);

	// confirm match password
	if ($_POST['pw'] !== $_POST['pw2'])
	{
		throw new Exception('Passwords must match', 500);
	}

	// connect db
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
	$result = $model->addItem((object)[
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
	$output->url = $_SERVER['PATH_URL'].$_SERVER['REQUEST_URI'];
	$output->query = $result->query;
	$output->success = $result->success;

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
