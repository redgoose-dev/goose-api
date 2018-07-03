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
	// check authorization
	$token = Auth::checkAuthorization($this->level->admin);

	// check srl
	if (!((int)$this->params['srl'] && $this->params['srl'] > 0))
	{
		throw new Exception('Not found srl', 404);
	}

	// set values
	// TODO: 여기서부터..
	$_PATCH = Util::getFormData();
	var_dump($_PATCH);
	exit;

	// set model and connect db
	$model = new Model();
	$model->connect();

	// check id
	$cnt = $model->getCount((object)[
		'table' => 'app',
		'where' => 'id="'.$_PATCH['id'].'"',
	]);
	if (isset($cnt->data) && $cnt->data > 0)
	{
		throw new Exception('`id` is already exist.', 500);
	}
	if ($_PATCH['id'] && !Util::allowString($_PATCH['id']))
	{
		throw new Exception('`id` can be used only in numbers and English.');
	}

	// set output
	$output = Controller::edit((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'app',
		'srl' => (int)$this->params['srl'],
		'data' => [
			$_PATCH['id'] ? "id='$_PATCH[id]'" : '',
			$_PATCH['name'] ? "name='$_PATCH[name]'" : '',
		],
	]);

	// set token
	if ($token) $output->_token = $token;

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}