<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get nests
 *
 * @var Goose $this
 */

try
{
	// get values
	$model = new Model();
	$page = (($_GET['page']) ? (int)$_GET['page'] : $this->defaults->page) - 1;
	$size = ($_GET['size']) ? (int)$_GET['size'] : $this->defaults->size;

	// connect db
	$tmp = $model->connect();
	if ($tmp)
	{
		throw new Exception($tmp->getMessage(), $tmp->getCode());
	}

	// set where
	// TODO: make where
	$where = '';

	// get datas
	$count = $model->getCount((object)[
		'table' => 'nest',
		'where' => $where,
		'debug' => __DEBUG__
	]);

	// get datas
	$items = $model->getItems((object)[
		'table' => 'nest',
		'field' => $_GET['field'],
		'json_field' => ['json'],
		'where' => '',
		'debug' => __DEBUG__
	]);

	// disconnect db
	$model->disconnect();

	// set output
	$output = (object)[
		'code' => 200,
		'count' => $count->data,
		'data' => $items->data,
	];
	if ($items->query) $output->query = $items->query;

	// output data
	Output::json($output, $_GET['min']);
}
catch (Exception $e)
{
	Output::json((object)[
		'code' => $e->getCode(),
		'message' => $e->getMessage()
	]);
}
