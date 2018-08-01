<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get user
 *
 * @var Goose $this
 */

try
{
	// check srl
	if (!((int)$this->params['srl'] && $this->params['srl'] > 0))
	{
		throw new Exception('Not found srl', 500);
	}

	// set model
	$model = new Model();
	$model->connect();

	// check authorization
	$token = null;
	$jwt = Token::get(__TOKEN__);
	if ($jwt->data->type !== 'user')
	{
		throw new Exception('You are not a logged in user.',401);
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

	// set output
	$output = Controller::item((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'user',
		'srl' => (int)$this->params['srl'],
	], function($result=null) {
		if (!isset($result->data)) return $result;
		if (isset($result->data->pw))
		{
			unset($result->data->pw);
		}
		return $result;
	});

	// set token
	if ($token->jwt) $output->_token = $token->jwt;

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
