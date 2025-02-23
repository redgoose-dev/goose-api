<?php
namespace Core;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * clear tokens
 * 블랙리스트에 등록된 토큰들을 삭제한다.
 *
 * @var Goose|Connect $this
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
    // TODO: 현재는 단순히 모두 삭제하고 있지만 만료시간을 검사하여 삭제하는것이 좋을거 같다.
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
	return Output::result($output);
}
catch(Exception $e)
{
  if ($this->model ?? false) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
