<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * add nest
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

	// set json
	$json = null;
	if (isset($_POST['json']))
	{
		$json = json_decode(urldecode($_POST['json']), false);
		if (!$json)
		{
			throw new Exception('The json syntax is incorrect.', 500);
		}
		$json = urlencode(json_encode($json, false));
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
		'table' => 'nest',
		'data' => (object)[
			'srl' => null,
			'app_srl' => $_POST['app_srl'],
			'id' => $_POST['id'],
			'name' => $_POST['name'],
			'description' => $_POST['description'],
			'json' => $json,
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
