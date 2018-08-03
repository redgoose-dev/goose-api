<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * edit app
 *
 * @var Goose $this
 */

try
{
	$tableName = 'apps';
	$srl = (int)$this->params['srl'];

	// check srl
	if (!($srl && $srl > 0))
	{
		throw new Exception('Not found srl', 204);
	}

	// id check
	if ($_POST['id'] && !Text::allowString($_POST['id']))
	{
		throw new Exception('`id` can be used only in numbers and English.', 204);
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

	// check app id
	$check_id = $model->getCount((object)[
		'table' => $tableName,
		'where' => "id LIKE '$_POST[id]' and srl!=".$srl,
	]);
	if (!!$check_id->data)
	{
		throw new Exception('The same name `id` is registered.', 204);
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
				$_POST['id'] ? "id='$_POST[id]'" : '',
				$_POST['name'] ? "name='$_POST[name]'" : '',
				$_POST['description'] ? "description='$_POST[description]'" : '',
			],
		]);
	}
	catch(Exception $e)
	{
		throw new Exception('Failed edit app', 204);
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