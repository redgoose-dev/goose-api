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
	// set model
	$model = new Model();
	$model->connect();

	// check authorization
	$token = Auth::checkAuthorization($model, 'user');

	// check value
	if (!$_POST['path'])
	{
		throw new Exception('The value `path` does not exist.', 204);
	}

	// set path
	$path = __PATH__.'/'.$_POST['path'];

	// check exist file
	if (!file_exists($path))
	{
		throw new Exception('There are no files in this path.', 204);
	}

	// delete file
	unlink($path);

	// set output
	$output = (object)[];
	$output->code = 200;
	if ($token) $output->_token = $token->jwt;

	// output
	Output::data($output);
}
catch (Exception $e)
{
	if (isset($model)) $model->disconnect();
	Error::data($e->getMessage(), $e->getCode());
}
