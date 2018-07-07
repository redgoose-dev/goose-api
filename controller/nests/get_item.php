<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get nest
 *
 * @var Goose $this
 */

try
{
	// check srl
	if (!((int)$this->params['srl'] && $this->params['srl'] > 0))
	{
		throw new Exception('Not found srl', 500);
	}

	// check authorization
	$token = Auth::checkAuthorization();

	// set output
	$output = Controller::item((object)[
		'goose' => $this,
		'table' => 'nest',
		'srl' => (int)$this->params['srl'],
		'jsonField' => ['json'],
	]);

	// set token
	if ($token) $output->_token = $token;

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
