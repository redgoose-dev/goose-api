<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * revoke token
 * 블랙리스트에 등록된 토큰들중에 만료된것만 찾아서 삭제한다.
 *
 * @var Goose $this
 */

try
{
	// set values
	$output = (object)[];

	// check authorization
	Auth::checkAuthorization($this->level->admin);

	// set model
	$model = new Model();
	$model->connect();

	// get datas
	$get_tokens = $model->getItems((object)[
		'table' => 'token',
		'field' => 'srl',
		'where' => 'expired < '.time()
	]);

	foreach ($get_tokens->data as $k=>$v)
	{
		$v = (object)$v;
		$model->delete((object)[
			'table' => 'token',
			'where' => 'srl='.(int)$v->srl,
			'debug' => __DEBUG__,
		]);
	}

	// set output
	$output->code = 200;

	// disconnect db
	$model->disconnect();

	// output data
	Output::data($output);
}
catch(Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
