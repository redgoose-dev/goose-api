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
// TODO: 레벨에서 더 높거나 낮은수치가 필요할지도 모르겠음..

// output
Controller::index($this, 'user', $where);
