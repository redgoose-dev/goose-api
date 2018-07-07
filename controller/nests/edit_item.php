<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * edit nest
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

	// set value
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
	$output = Controller::edit((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'nest',
		'srl' => (int)$this->params['srl'],
		'data' => [
			$_POST['app_srl'] ? "app_srl='$_POST[app_srl]'" : '',
			$_POST['id'] ? "id='$_POST[id]'" : '',
			$_POST['name'] ? "name='$_POST[name]'" : '',
			$_POST['json'] ? "json='$json'" : '',
		],
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
