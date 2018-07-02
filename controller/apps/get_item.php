<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get app
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
		'table' => 'app',
		'field' => $_GET['field'],
		'where' => 'srl='.(int)$this->params['srl'],
		'debug' => false,
	]);

	// disconnect db
	$model->disconnect();

	// output data
	Output::data((object)[
		'code' => 200,
		'data' => $item,
	]);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
