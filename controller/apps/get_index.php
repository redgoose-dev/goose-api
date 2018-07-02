<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get apps
 *
 * url params
 * - @param string id
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
	if ($id = Util::getParameter('id'))
	{
		$where .= ' and id LIKE \''.$id.'\'';
	}
	if ($name = Util::getParameter('name'))
	{
		$where .= ' and name LIKE \'%'.$name.'%\'';
	}

	// set output
	$output = Controller::index((object)[
		'goose' => $this,
		'auth' => false,
		'table' => 'app',
		'where' => $where,
		'page' => 1,
		'size' => 20,
		'field' => $_GET['field'],
	]);
	if ($token) $output->_token = $token;

	// output
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
