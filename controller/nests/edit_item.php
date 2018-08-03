<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * edit nest
 *
 * @var Goose $this
 */

try
{
	$tableName = 'nests';
	$srl = (int)$this->params['srl'];

	// check srl
	if (!($srl && $srl > 0))
	{
		throw new Exception('Not found srl', 204);
	}

	// check post values
	Util::checkExistValue($_POST, [ 'app_srl', 'id', 'name' ]);

	// check `id`
	if (!Text::allowString($_POST['id']))
	{
		throw new Exception('`id` can be used only in numbers and English.');
	}

	// check,set json
	$json = null;
	if (isset($_POST['json']))
	{
		$json = json_decode(urldecode($_POST['json']), false);
		if (!$json)
		{
			throw new Exception('The json syntax is incorrect.', 204);
		}
		$json = urlencode(json_encode($json, false));
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

	// check app
	$cnt = $model->getCount((object)[
		'table' => 'apps',
		'where' => 'srl='.(int)$_POST['app_srl']
	]);
	if (!$cnt->data)
	{
		throw new Exception('There is no `apps` data.', 204);
	}

	// check duplicate nest id
	$cnt = $model->getCount((object)[
		'table' => $tableName,
		'where' => 'id="'.$_POST['id'].'" and srl!='.$srl,
	]);
	if ($cnt->data)
	{
		throw new Exception('There is a duplicate `id`.', 204);
	}

	// set output
	$output = Controller::edit((object)[
		'goose' => $this,
		'model' => $model,
		'table' => $tableName,
		'srl' => $srl,
		'data' => [
			$_POST['app_srl'] ? "app_srl='$_POST[app_srl]'" : '',
			$_POST['id'] ? "id='$_POST[id]'" : '',
			$_POST['name'] ? "name='$_POST[name]'" : '',
			$_POST['description'] ? "description='$_POST[description]'" : '',
			$_POST['json'] ? "json='$json'" : '',
		]
	]);

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
