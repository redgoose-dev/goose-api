<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * delete app
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

	// TODO: 파라메터를 하나 더 만들어서 데이터 삭제할지 선택권 만들어야함.
	// TODO: 하위 데이터를 삭제한다면 nests, categories, articles 삭제하기
	// TODO: 하위 데이터를 삭제안한다면 nests, articles 에 있는 app_srl 값 삭제하기

	// remove item
	$output = Controller::delete((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'app',
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
