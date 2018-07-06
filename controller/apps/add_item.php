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
	// check post values
	Util::checkExistValue($_POST, [ 'id', 'name' ]);

	// id check
	if (!Text::allowString($_POST['id']))
	{
		throw new Exception('`id` can be used only in numbers and English.');
	}

	// set model
	$model = new Model();
	$model->connect();

	// check authorization
	$token = Auth::checkAuthorization($this->level->admin, $model);

	// set output
	$output = Controller::add((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'app',
		'data' => (object)[
			'srl' => null,
			'id' => $_POST['id'],
			'name' => $_POST['name'],
			'regdate' => date('YmdHis'),
		]
	]);

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
