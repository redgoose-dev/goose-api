<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * add app
 *
 * @var Goose $this
 */

try
{
	// check authorization
	$token = Auth::checkAuthorization($this->level->admin);

	// check post values
	Util::checkExistValue($_POST, [ 'id', 'name' ]);

	// set values
	$output = (object)[];

	// set model and connect db
	$model = new Model();
	$model->connect();

	// id check
	$cnt = $model->getCount((object)[
		'table' => 'app',
		'where' => 'id="'.$_POST['id'].'"',
	]);
	if (isset($cnt->data) && $cnt->data > 0)
	{
		throw new Exception('`id` is already exist.', 500);
	}
	if (!Util::allowString($_POST['id']))
	{
		throw new Exception('`id` can be used only in numbers and English.');
	}

	// add data
	$result = $model->add((object)[
		'table' => 'app',
		'data' => (object)[
			'srl' => null,
			'id' => $_POST['id'],
			'name' => $_POST['name'],
			'regdate' => date('YmdHis'),
		],
		'debug' => __DEBUG__
	]);

	// disconnect db
	$model->disconnect();

	// set output
	$output->code = 200;
	$output->query = $result->query;
	if ($token) $output->_token = $token;

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}