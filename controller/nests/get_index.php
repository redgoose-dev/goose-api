<?php
namespace Core;

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

$where = '';
if ($app = Util::getParameter('app'))
{
	$where .= ' and app_srl='.$app;
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
Controller::index((object)[
	'goose' => $this,
	'table' => 'nest',
	'where' => $where,
	'jsonField' => ['json']
]);
