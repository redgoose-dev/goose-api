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
Controller::index($this, 'app', $where);
