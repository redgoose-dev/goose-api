<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * delete nest
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

	// TODO: articles, categories, files 데이터 삭제

	// remove item
	$output = Controller::delete((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'nests',
		'srl' => (int)$this->params['srl'],
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