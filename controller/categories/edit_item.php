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
	$tableName = 'categories';
	$srl = (int)$this->params['srl'];

	// check srl
	if (!($srl && $srl > 0))
	{
		throw new Exception('Not found srl', 204);
	}

	// set model
	$model = new Model();
	$model->connect();

	// check access
	$token = Controller::checkAccessItem((object)[
		'model' => $model,
		'table' => $tableName,
		'srl' => $srl,
	]);

	// check exist nest
	if (isset($_POST['nest_srl']))
	{
		$cnt = $model->getCount((object)[
			'table' => 'nests',
			'where' => 'srl='.(int)$_POST['nest_srl'],
		]);
		if (!$cnt->data)
		{
			throw new Exception('There is no `nest` data.', 204);
		}
	}

	// set output
	try
	{
		$output = Controller::edit((object)[
			'goose' => $this,
			'model' => $model,
			'table' => $tableName,
			'srl' => $srl,
			'data' => [
				isset($_POST['nest_srl']) ? 'nest_srl='.(int)$_POST['nest_srl'] : '',
				isset($_POST['name']) ? "name='$_POST[name]'" : '',
			],
		]);
	}
	catch(Exception $e)
	{
		throw new Exception('Failed edit category', 204);
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
