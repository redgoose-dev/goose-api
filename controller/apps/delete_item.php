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
		throw new Exception('Not found srl', 204);
	}

	// set model
	$model = new Model();
	$model->connect();

	// get app data
	$app = $model->getItem((object)[
		'table' => 'apps',
		'field' => 'user_srl',
		'where' => 'srl='.(int)$this->params['srl'],
	]);
	if (!$app = $app->data)
	{
		throw new Exception('There is no `apps` data.', 204);
	}

	// check authorization
	$token = null;
	$jwt = Token::get(__TOKEN__);
	if ((int)$jwt->data->user_srl === (int)$app->user_srl)
	{
		$token = Auth::checkAuthorization($model, 'user'); // self
	}
	else
	{
		$token = Auth::checkAuthorization($model, 'admin'); // admin
	}

	// TODO: 현재는 app만 삭제하도록 되어있음
	// TODO: 파라메터를 하나 더 만들어서 데이터 삭제할지 선택권 만들어야함. (remove_children)
	// TODO: 하위 데이터를 삭제한다면 nests, categories, articles 삭제하기
	// TODO: 하위 데이터를 삭제안한다면 nests, articles 에 있는 app_srl 값 삭제하기
	// TODO: 만약 하위 데이터를 삭제안한다면 어떻게 할지도 생각해야함. (app_srl을 null로 변경한다던지, 옵션을 빼고 몽땅 삭제한다던지 결정 필요함.)

	// remove item
	$output = Controller::delete((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'apps',
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
