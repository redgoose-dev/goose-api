<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * remove file
 *
 * @_POST string path
 *
 * @var Goose $this
 */

try
{
	// check value
	if (!$_POST['path'])
	{
		throw new Exception('The value `path` does not exist.');
	}

	// set path
	$path = __PATH__.'/'.$_POST['path'];

	// check exist file
	if (!file_exists($path))
	{
		throw new Exception('There are no files in this path.');
	}

	// delete file
	unlink($path);

	// set output
	$output = (object)[];
	$output->code = 200;

	// output
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}