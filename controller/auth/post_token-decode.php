<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * decode token
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
	$jwt = Token::get($_SERVER['HTTP_AUTHORIZATION']);

	// set output
	$output->code = 200;
	$output->data = $jwt;
	if ($token) $output->_token = $token;

	// output
	Output::data($output);
}
catch(Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
