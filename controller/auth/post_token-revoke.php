<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * revoke token
 * 블랙리스트에 등록된 토큰들중에 만료된것만 찾아서 삭제한다.
 *
 * TODO: 사용 역할은 블랙리스트에 들어있는 토큰들을 삭제한다.
 * TODO: 조건은 만료시간이 지난 토큰만 삭제한다.
 *
 * @var Goose $this
 */

try
{
	// set values
	$output = (object)[];

	// check authorization
	Auth::checkAuthorization($this->level->admin);

	// TODO: 만료시간이 지난걸 db에서 찾기 (field:srl)

	// set output
	$output->code = 200;

	// output data
	Output::data($output);
}
catch(Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
