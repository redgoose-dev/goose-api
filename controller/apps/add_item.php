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
		throw new Exception('`id` can be used only in numbers and English.', 204);
	}

	// set model
	$model = new Model();
	$model->connect();

	// check authorization
	$token = Auth::checkAuthorization($model, 'user');

	// check id
	$cnt = $model->getCount((object)[
		'table' => 'apps',
		'where' => "id LIKE '$_POST[id]'",
	]);
	if (!!$cnt->data)
	{
		throw new Exception('The same name `id` is registered.', 204);
	}

	// set output
	try
	{
		$output = Controller::add((object)[
			'goose' => $this,
			'model' => $model,
			'table' => 'apps',
			'data' => (object)[
				'srl' => null,
				'user_srl' => (int)$token->data->user_srl,
				'id' => $_POST['id'],
				'name' => $_POST['name'],
				'description' => $_POST['description'],
				'regdate' => date('Y-m-d H:i:s'),
			]
		]);
	}
	catch(Exception $e)
	{
		throw new Exception('Failed add app', 204);
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
