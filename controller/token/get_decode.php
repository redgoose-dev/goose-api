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
	// set model
	$model = new Model();
	$model->connect();

	// check authorization
	$token = Auth::checkAuthorization($model);

	// set values
	$output = (object)[];

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
	if (isset($model)) $model->disconnect();
	Error::data($e->getMessage(), $e->getCode());
}