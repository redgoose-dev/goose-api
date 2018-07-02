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
	Auth::checkAuthorization();

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

	// output
	Controller::index((object)[
		'goose' => $this,
		'table' => 'category',
		'where' => $where
	]);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}