<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * delete user
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

	// check data
	$cnt = $model->getCount((object)[
		'table' => 'user',
		'where' => 'srl='.(int)$this->params['srl'],
	])->data;
	if (!$cnt) throw new Exception('No user data.', 204);

	// check authorization
	$token = null;
	$jwt = Token::get(__TOKEN__);
	if ($jwt->data->type !== 'user')
	{
		throw new Exception('You are not a logged in user.',204);
	}
	if ((int)$jwt->data->user_srl === (int)$this->params['srl'])
	{
		// 본인일때..
		$token = Auth::checkAuthorization($model);
	}
	else
	{
		// 자신의 데이터가 아닐때 관리자 검사를 한다.
		$token = Auth::checkAuthorization($model, 'admin');
	}

	// remove item
	$output = Controller::delete((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'user',
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
