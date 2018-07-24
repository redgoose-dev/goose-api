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
	if ((int)$jwt->data->user_srl === (int)$this->params['srl'])
	{
		// 사용자 레벨 검사
		$token = Auth::checkAuthorization((int)$jwt->data->level, $model);
	}
	else
	{
		// 관리자 레벨 검사
		$token = Auth::checkAuthorization($this->level->admin, $model);
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
	if ($token) $output->_token = $token;

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
