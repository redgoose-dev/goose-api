<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * Nests
 *
 * @var Goose $this
 */

try
{
	// get instance
	$model = new Model();

	// connect db
	$tmp = $model->connect();
	if ($tmp)
	{
		throw new Exception($tmp->getMessage(), $tmp->getCode());
	}

	// get datas
	$count = $model->getCount((object)[
		'table' => 'nest',
		'debug' => false
	]);

	// get datas
	$items = $model->getItems((object)[
		'table' => 'nest',
		'field' => $_GET['field'],
		'json_field' => ['json'],
		'where' => 'srl=1',
		'debug' => false,
	]);

	// disconnect db
	$model->disconnect();

	// output data
	Output::json((object)[
		'code' => 200,
		'count' => $count,
		'items' => $items,
	], $_GET['min']);
}
catch (Exception $e)
{
	Output::json((object)[
		'code' => $e->getCode(),
		'message' => $e->getMessage()
	]);
}
