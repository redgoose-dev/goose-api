<?php
namespace Core;

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
Controller::index((object)[
	'goose' => $this,
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
