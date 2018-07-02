<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * make token
 * 관리자 권한을 가지고 있는 토큰이 사용자나 익명 토큰을 만들어준다.
 * 그런데 사용자 토큰을 만드는건 로그인과 별반 차이없는 기능이고, 공개용 토큰 제작은 cli 로 제작 가능하다.
 * 사용 목적성이 없어지는 느낌인데.. `TOKEN_ID`값이 변하면 공개용 토큰을 교체해줘야 하는데 cli 에서 할 수 있는것이기 때문에 필요할까 싶음..
 * 정 필요없으면 삭제할 예정
 *
 * data params
 * - @param string email
 * - @param string password
 *
 * @var Goose $this
 */

try
{
	// set values
	$output = (object)[];
	$data = (object)[];

	// check authorization
	Auth::checkAuthorization($this->level->admin);

	// is user
	if ($_POST['email'] && $_POST['password'])
	{
		$user = Auth::login((object)[
			'email' => $_POST['email'],
			'password' => $_POST['password']
		]);
		$data->type = 'user';
		$data->user_srl = (int)$user->srl;
		$data->email = $user->email;
		$data->level = (int)$user->level;
	}
	else
	{
		$data->type = 'anonymous';
	}

	// make token
	$jwt = Token::make((object)[
		'time' => true,
		'exp' => ($data->type === 'user'),
		'data' => $data,
	]);

	// set output
	$output->code = 200;
	if (__DEBUG__) $output->option = $jwt->option;
	$output->data = $jwt->token;

	// output data
	Output::data($output);
}
catch(Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}