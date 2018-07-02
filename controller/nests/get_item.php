<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get nest
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

	if (!((int)$this->params['srl'] && $this->params['srl'] > 0))
	{
		throw new Exception('Not found srl', 404);
	}

	// get data
	$item = $model->getItem((object)[
		'table' => 'nest',
		'field' => $_GET['field'],
		'json_field' => ['json'],
		'where' => 'srl='.(int)$this->params['srl'],
		'debug' => __DEBUG__
	]);

	// disconnect db
	$model->disconnect();

	// set output
	$output = (object)[
		'code' => 200,
		'data' => $item->data,
	];
	if ($item->query) $output->query = $item->query;

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
