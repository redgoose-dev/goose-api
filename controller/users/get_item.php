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
	$level = $jwt->data->level ? (int)$jwt->data->level : 0;
	if ((int)$jwt->data->user_srl === (int)$this->params['srl'])
	{
		// (본인) 레벨검사이지만 거의패스
		$token = Auth::checkAuthorization(0, $model);
	}
	else
	{
		// (관리자) 레벨검사
		$token = Auth::checkAuthorization(1, $model);
	}

	// set output
	$output = Controller::item((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'user',
		'srl' => (int)$this->params['srl'],
		'where' => ' and level<='.$level,
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
