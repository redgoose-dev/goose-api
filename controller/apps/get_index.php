<?php
namespace Core;

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

// output
Controller::index((object)[
	'goose' => $this,
	'table' => 'app',
	'where' => $where,
	'page' => 1,
	'size' => 20,
	'field' => $_GET['field'],
]);
