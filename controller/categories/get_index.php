<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get categories
 *
 * url params
 * - @param int nest
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
	if ($nest = Util::getParameter('nest'))
	{
		$where .= ' and nest_srl='.$nest;
	}
	if ($name = Util::getParameter('name'))
	{
		$where .= ' and name LIKE \'%'.$name.'%\'';
	}

	// set output
	$output = Controller::index((object)[
		'goose' => $this,
		'table' => 'category',
		'where' => $where
	]);

	// set token
	if ($token) $output->_token = $token;

	// output
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}