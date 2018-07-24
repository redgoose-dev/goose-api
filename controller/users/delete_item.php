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
		throw new Exception('Not found srl', 500);
	}

	// set model
	$model = new Model();
	$model->connect();

	// check authorization
	$token = Auth::checkAuthorization($this->level->admin, $model);

	// check user count
	// 관리자 사용자가 하나도 없으면 안되기 때문에 검사하기
	$cnt = $model->getCount((object)[
		'table' => 'user',
		'where' => '`level`>='.$this->level->admin,
		'debug' => true,
	]);
	if ($cnt->data < 2)
	{
		throw new Exception('You can not delete the administrator user because it is gone.', 204);
	}

	// remove item
	$output = Controller::delete((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'user',
		'srl' => (int)$this->params['srl'],
	]);

	// set output
	if ($token) $output->_token = $token;

	// disconnect db
	$model->disconnect();

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
