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
	// set output
	$output = (object)[];
	$output->code = 200;
	$output->message = 'hello goose api';

	// output
	Output::data($output);
}
catch(Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}