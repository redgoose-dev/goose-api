<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * edit category
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

	// set model
	$model = new Model();
	$model->connect();

	// check authorization
	$token = Auth::checkAuthorization($this->level->admin, $model);

	// check exist nest
	if ($_POST['nest_srl'])
	{
		$nestCount = $model->getCount((object)[
			'table' => 'nests',
			'where' => 'srl='.(int)$_POST['nest_srl'],
			'debug' => __DEBUG__,
		]);
		if (!$nestCount->data)
		{
			throw new Exception('There is no `nest` data.', 500);
		}
	}

	// set output
	$output = Controller::edit((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'categories',
		'srl' => (int)$this->params['srl'],
		'data' => [
			$_POST['nest_srl'] ? "nest_srl='$_POST[nest_srl]'" : '',
			$_POST['name'] ? "name='$_POST[name]'" : '',
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
