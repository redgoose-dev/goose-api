<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get json
 *
 * url params
 * - @param string name
 *
 * @var Goose $this
 */

try
{
	// check authorization
	$token = Auth::checkAuthorization();

	// set where
	$where = '';
	if ($name = Util::getParameter('name'))
	{
		$where .= ' and name LIKE \'%'.$name.'%\'';
	}

	// output
	$output = Controller::index((object)[
		'goose' => $this,
		'table' => 'json',
		'where' => $where,
		'jsonField' => ['json']
	]);

	// set token
	if ($token) $output->_token = $token;

	// output
	Output::data($output);
}
catch(Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
