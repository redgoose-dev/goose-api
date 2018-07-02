<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * make token
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
	Auth::checkAuthorization(100);

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
		'exp' => ($data->type === 'user'),
		'time' => true,
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