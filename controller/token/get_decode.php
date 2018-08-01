<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * decode token
 *
 *
 * @var Goose $this
 */

try
{
	// set values
	$output = (object)[];

	// check authorization
	$token = Auth::checkAuthorization();

	// get decode token
	$jwt = Token::get(__TOKEN__);

	// set output
	$output->code = 200;
	$output->data = $jwt->data;
	if ($token) $output->_token = $token->jwt;

	// output
	Output::data($output);
}
catch(Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}