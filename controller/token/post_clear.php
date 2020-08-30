<?php
namespace Core;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * clear tokens
 * 블랙리스트에 등록된 토큰들중에 만료된것만 찾아서 삭제한다.
 *
 * @var Goose $this
 */

try
{
  // connect db
  $this->model->connect();

	// check authorization
	$token = Auth::checkAuthorization($this->model, 'admin');

	// set values
	$output = (object)[];

	// get data
	$get_tokens = $this->model->getItems((object)[
		'table' => 'tokens',
		'field' => 'srl',
    'debug' => __API_DEBUG__,
	]);

	foreach ($get_tokens->data as $k=>$v)
	{
		$v = (object)$v;
    $this->model->delete((object)[
			'table' => 'tokens',
			'where' => 'srl='.(int)$v->srl,
			'debug' => __API_DEBUG__,
		]);
	}

	// set output
	$output->code = 200;

  // set token
	if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

	// output data
	Output::data($output);
}
catch(Exception $e)
{
  $this->model->disconnect();
	Error::data($e->getMessage(), $e->getCode());
}
