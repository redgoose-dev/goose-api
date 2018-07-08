<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get intro
 *
 * TODO: 작업이 마무리 되면 어떻게 활용할 수 있을지 고민해봐야함..
 *
 * @var Goose $this
 */

try
{
	// check authorization
	$token = Auth::checkAuthorization();

	// set values
	$output = (object)[];

	// set output
	$output->code = 200;
	$output->message = 'Welcome to goose api';
	$output->token_decorded = Token::get(__TOKEN__);
	if ($token) $output->_token = $token;

	// output
	Output::data($output);
}
catch(Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}