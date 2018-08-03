<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * clear tokens
 * 블랙리스트에 등록된 토큰들중에 만료된것만 찾아서 삭제한다.
 *
 * @var Goose $this
 */

try
{
	// set model
	$model = new Model();
	$model->connect();

	// check authorization
	$token = Auth::checkAuthorization($model, 'admin');

	// set values
	$output = (object)[];

	// get datas
	$get_tokens = $model->getItems((object)[
		'table' => 'tokens',
		'field' => 'srl',
		'where' => 'expired < '.time()
	]);

	foreach ($get_tokens->data as $k=>$v)
	{
		$v = (object)$v;
		$model->delete((object)[
			'table' => 'tokens',
			'where' => 'srl='.(int)$v->srl,
			'debug' => __DEBUG__,
		]);
	}

	// set output
	$output->code = 200;
	if ($token) $output->_token = $token->jwt;

	// disconnect db
	$model->disconnect();

	// output data
	Output::data($output);
}
catch(Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
