<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * hello word
 *
 * @var Goose $this
 */

try
{
	// check authorization
	$token = Auth::checkAuthorization();

	// set output
	$output = (object)[];
	$output->code = 200;
	$output->message = 'hello world';
	if ($token) $output->_token = $token->jwt;

	// output
	Output::data($output);
}
catch(Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}