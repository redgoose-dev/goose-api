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
	$tableName = 'apps';
	$srl = (int)$this->params['srl'];

	// check srl
	if (!($srl && $srl > 0))
	{
		throw new Exception('Not found srl', 204);
	}

	// set model
	$model = new Model();
	$model->connect();

	// check access
	$token = Controller::checkAccessItem((object)[
		'model' => $model,
		'table' => $tableName,
		'srl' => $srl,
	]);

	// TODO: 현재는 app만 삭제하도록 되어있음
	// TODO: 파라메터를 하나 더 만들어서 데이터 삭제할지 선택권 만들어야함. (remove_children)
	// TODO: 하위 데이터를 삭제한다면 nests, categories, articles 삭제하기
	// TODO: 하위 데이터를 삭제안한다면 nests, articles 에 있는 app_srl 값 삭제하기
	// TODO: 만약 하위 데이터를 삭제안한다면 어떻게 할지도 생각해야함. (app_srl을 null로 변경한다던지, 옵션을 빼고 몽땅 삭제한다던지 결정 필요함.)

	// remove item
	$output = Controller::delete((object)[
		'goose' => $this,
		'model' => $model,
		'table' => $tableName,
		'srl' => $srl,
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
