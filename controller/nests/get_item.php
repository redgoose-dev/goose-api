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
	$tableName = 'nests';
	if ($this->params['srl'] && (int)$this->params['srl'] > 0)
	{
		$srl = (int)$this->params['srl'];
	}
	else if ($this->params['id'])
	{
		$id = $this->params['id'];
	}
	else
	{
		throw new Exception('Not found srl or id', 500);
	}

	// set model
	$model = new Model();
	$model->connect();

	// check access
	$token = Controller::checkAccessItem((object)[
		'model' => $model,
		'table' => $tableName,
		'srl' => isset($srl) ? $srl : null,
		'id' => isset($id) ? $id : null,
		'useStrict' => true,
	]);

	// set output
	$output = Controller::item((object)[
		'goose' => $this,
		'model' => $model,
		'table' => $tableName,
		'srl' => isset($srl) ? $srl : null,
		'id' => isset($id) ? $id : null,
		'json_field' => ['json'],
	]);

	// set token
	if ($token) $output->_token = $token->jwt;

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
