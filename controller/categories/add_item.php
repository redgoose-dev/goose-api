<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * add category
 *
 * @var Goose $this
 */

try
{
	// check post values
	Util::checkExistValue($_POST, [ 'nest_srl', 'name' ]);

	// set model and connect db
	$model = new Model();
	$model->connect();

	// check authorization
	$token = Auth::checkAuthorization($model, 'user');

	// check exist nest
	$cnt = $model->getCount((object)[
		'table' => 'nests',
		'where' => 'srl='.(int)$_POST['nest_srl'],
	]);
	if (!$cnt->data)
	{
		throw new Exception('There is no `nest` data.', 204);
	}

	// get max turn
	$tableName = $model->getTableName('categories');
	$max = 'select max(turn) as maximum from '.$tableName.' where nest_srl='.(int)$_POST['nest_srl'];
	$max = $model->db->prepare($max);
	$max->execute();
	$max = (int)$max->fetchColumn();
	$max += 1;

	// set output
	try
	{
		$output = Controller::add((object)[
			'goose' => $this,
			'model' => $model,
			'table' => 'categories',
			'data' => (object)[
				'srl' => null,
				'nest_srl' => $_POST['nest_srl'],
				'user_srl' => (int)$token->data->user_srl,
				'turn' => $max,
				'name' => $_POST['name'],
				'regdate' => date('YmdHis'),
			]
		]);
	}
	catch(Exception $e)
	{
		throw new Exception('Failed add category', 204);
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
