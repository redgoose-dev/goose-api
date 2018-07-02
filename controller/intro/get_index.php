<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get intro
 *
 * @var Goose $this
 */

try
{
	// check authorization
	$token = Auth::checkAuthorization(100);

	// set values
	$output = (object)[];

	// set output
	$output->code = 200;
	$output->message = 'Welcome to goose api';
	if ($token) $output->_token = $token;

	// output
	Output::data($output);
}
catch(Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}