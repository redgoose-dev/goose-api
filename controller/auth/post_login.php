<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * post login
 * 이메일과 패스워드로 로그인 검사를 하고, 사용자 정보와 새로 만들어진 유저용 토큰을 출력한다.
 *
 * @var Goose $this
 */

try
{
	// check authorization
	Auth::checkAuthorization();

	// set values
	$output = (object)[];
	$data = (object)[];

	// check post values
	Util::checkExistValue($_POST, [ 'email', 'pw' ]);

	// get user data
	$user = Auth::login((object)[
		'email' => $_POST['email'],
		'password' => $_POST['pw']
	]);

	// make token
	$jwt = Token::make((object)[
		'exp' => true,
		'time' => true,
		'data' => (object)[
			'type' => 'user',
			'user_srl' => $user->srl,
			'email' => $user->email,
			'level' => $user->level,
		],
	]);

	// set data
	$data->srl = (int)$user->srl;
	$data->email = $user->email;
	$data->name = $user->name;
	$data->level = (int)$user->level;
	$data->token = $jwt->token;

	// set output
	$output->code = 200;
	$output->data = $data;

	// output
	Output::data($output);
}
catch(Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
