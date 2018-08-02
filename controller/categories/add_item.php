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
	$token = Auth::checkAuthorization($this->level->admin, $model);

	// check exist nest
	$nestCount = $model->getCount((object)[
		'table' => 'nests',
		'where' => 'srl='.(int)$_POST['nest_srl'],
		'debug' => __DEBUG__,
	]);
	if (!$nestCount->data)
	{
		throw new Exception('There is no `nest` data.', 500);
	}

	// get max turn
	$max = 'select max(turn) as maximum from '.$model->getTableName('categories').' where nest_srl='.(int)$_POST['nest_srl'];
	$max = $model->db->prepare($max);
	$max->execute();
	$max = (int)$max->fetchColumn();
	$max += 1;

	// set output
	$output = Controller::add((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'categories',
		'data' => (object)[
			'srl' => null,
			'nest_srl' => $_POST['nest_srl'],
			'turn' => $max,
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
