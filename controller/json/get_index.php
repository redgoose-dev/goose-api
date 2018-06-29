<?php
namespace Core;

if (!defined('__GOOSE__')) exit();

/**
 * get json
 *
 * url params
 * - @param string name
 *
 * @var Goose $this
 */

// set where
$where = '';
if ($name = Util::getParameter('name'))
{
	$where .= ' and name LIKE \'%'.$name.'%\'';
}

// output
Controller::index((object)[
	'goose' => $this,
	'table' => 'json',
	'where' => $where,
	'jsonField' => ['json']
]);
