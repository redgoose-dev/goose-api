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
	// check srl
	if (!((int)$this->params['srl'] && $this->params['srl'] > 0))
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

	// get nest data
	$nest = $model->getItem((object)[
		'table' => 'nests',
		'field' => 'user_srl',
		'where' => 'srl='.(int)$this->params['srl'],
	]);
	if (!$nest = $nest->data)
	{
		throw new Exception('There is no `nests` data.', 204);
	}

	// check authorization
	$token = null;
	$jwt = Token::get(__TOKEN__);
	if ((int)$jwt->data->user_srl === (int)$nest->user_srl)
	{
		$token = Auth::checkAuthorization($model, 'user'); // self
	}
	else
	{
		$token = Auth::checkAuthorization($model, 'admin'); // admin
	}

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
		'table' => 'nests',
		'where' => 'id="'.$_POST['id'].'" and srl!='.(int)$this->params['srl'],
	]);
	if ($cnt->data)
	{
		throw new Exception('There is a duplicate `id`.', 204);
	}

	// set output
	$output = Controller::edit((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'nests',
		'srl' => (int)$this->params['srl'],
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
