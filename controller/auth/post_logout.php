<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * post logout
 * 주 목적은 만료되지도 않은 토큰 사용을 막기위하여 블랙리스트용 토큰을 추가하기 위함.
 * 유저 로그인이라면 블랙리스트에 시그니쳐값을 추가하고 익명 토큰을 만들어서 결과값으로 출력한다.
 *
 * @var Goose $this
 */

try
{
	// set values
	$output = (object)[];
	$token = $_SERVER['HTTP_AUTHORIZATION'];
	$sign = explode('.', $token)[2];

	// get decode token
	$jwt = Token::get($token);

	// if user token
	if ($jwt->data->type !== 'user')
	{
		throw new Exception('This is not a user token.');
	}
	if (!$jwt->exp)
	{
		throw new Exception('Token without expiration time.');
	}

	// make model and connect db
	$model = new Model();
	$model->connect();

	// check blacklist token
	$blacklistToken = $model->getCount((object)[
		'table' => 'token',
		'where' => 'token LIKE \''.$sign.'\''
	]);
	if ($blacklistToken->data)
	{
		throw new Exception('Blacklist has token.');
	}

	// add token to blacklist
	$model->addItem((object)[
		'table' => 'token',
		'data' => (object)[
			'srl' => null,
			'token' => $sign,
			'expired' => $jwt->exp,
		],
	]);

	// make new public token
	$newToken = Token::make((object)[
		'time' => true,
		'exp' => false,
	]);

	// set output
	$output->code = 200;
	$output->token = $newToken->token;

	// output
	Output::data($output);
}
catch(Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
