<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get users
 *
 * url params
 * - @param string email
 * - @param string name
 * - @param int level
 *
 * @var Goose $this
 */

try
{
	// check authorization
	$token = Auth::checkAuthorization($this->level->admin);

	// set where
	$where = '';
	if ($email = Util::getParameter('email'))
	{
		$where .= ' and email LIKE \''.$email.'\'';
	}
	if ($name = Util::getParameter('name'))
	{
		$where .= ' and name LIKE \'%'.$name.'%\'';
	}
	if ($level = Util::getParameter('level'))
	{
		$where .= ' and level='.$level;
	}

	// output
	$output = Controller::index((object)[
		'goose' => $this,
		'auth' => true,
		'table' => 'user',
		'where' => $where,
	], function($result=null) {
		if (!isset($result->data)) return $result;
		foreach ($result->data as $k=>$o)
		{
			// pw 항목 삭제
			if (isset($result->data[$k]['pw']))
			{
				unset($result->data[$k]['pw']);
			}
		}
		return $result;
	});

	// set token
	if ($token) $output->_token = $token;

	// output
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
