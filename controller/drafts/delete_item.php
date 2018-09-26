<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * delete draft
 *
 * @var Goose $this
 */

try
{
	$tableName = 'drafts';
	$srl = (int)$this->params['srl'];

	// check srl
	if (!($srl && $srl > 0))
	{
		throw new Exception('Not found srl', 500);
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

	// remove files
	Controller::removeAttachFiles($model, $srl, 'draft');

	// remove item
	$output = Controller::delete((object)[
		'goose' => $this,
		'model' => $model,
		'table' => $tableName,
		'srl' => $srl,
	]);

	// set output
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
