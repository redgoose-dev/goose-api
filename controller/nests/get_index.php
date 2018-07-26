<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get nests
 *
 * url params
 * - @param int app
 * - @param string id
 * - @param string name
 *
 * @var Goose $this
 */

try
{
	// check authorization
	$token = Auth::checkAuthorization();

	$where = '';
	if ($app = Util::getParameter('app'))
	{
		$where .= ($app === 'NULL') ? ' and app_srl IS NULL' : ' and app_srl='.$app;
	}
	if ($id = Util::getParameter('id'))
	{
		$where .= ' and id LIKE \''.$id.'\'';
	}
	if ($name = Util::getParameter('name'))
	{
		$where .= ' and name LIKE \'%'.$name.'%\'';
	}

	// output
	$output = Controller::index((object)[
		'goose' => $this,
		'table' => 'nest',
		'where' => $where,
		'json_field' => ['json']
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
