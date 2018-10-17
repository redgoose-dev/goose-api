<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * add json
 *
 * @var Goose $this
 */

try
{
	// check post values
	Util::checkExistValue($_POST, [ 'name', 'json' ]);

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
	$token = Auth::checkAuthorization($model, 'user');

	// set output
	try
	{
		$output = Controller::add((object)[
			'goose' => $this,
			'model' => $model,
			'table' => 'json',
			'data' => (object)[
				'srl' => null,
				'user_srl' => $token->data->user_srl,
				'name' => $_POST['name'],
				'description' => $_POST['description'],
				'json' => $json,
				'regdate' => date('YmdHis'),
			]
		]);
	}
	catch(Exception $e)
	{
		throw new Exception('Failed add json.', 204);
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
	if (isset($model)) $model->disconnect();
	Error::data($e->getMessage(), $e->getCode());
}
