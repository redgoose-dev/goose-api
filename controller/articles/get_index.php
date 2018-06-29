<?php
namespace Core;

if (!defined('__GOOSE__')) exit();

/**
 * get articles
 *
 * url params
 * - @param int app
 * - @param int nest
 * - @param int category
 * - @param int user
 * - @param string title
 * - @param string content
 * - @param string ip
 *
 * @var Goose $this
 */

// set where
$where = '';
if ($app = Util::getParameter('app'))
{
	$where .= ' and app_srl='.$app;
}
if ($nest = Util::getParameter('nest'))
{
	$where .= ' and nest_srl='.$nest;
}
if ($category = Util::getParameter('category'))
{
	$where .= ' and category_srl='.$category;
}
if ($user = Util::getParameter('user'))
{
	$where .= ' and user_srl='.$user;
}
if ($title = Util::getParameter('title'))
{
	$where .= ' and title LIKE \'%'.$title.'%\'';
}
if ($content = Util::getParameter('content'))
{
	$where .= ' and content LIKE \'%'.$content.'%\'';
}


// output
Controller::index((object)[
	'goose' => $this,
	'table' => 'article',
	'where' => $where,
	'jsonField' => ['json']
]);
