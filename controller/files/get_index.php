<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get files
 *
 * url params
 * - @param int article
 * - @param string name
 * - @param string type
 * - @param string ready
 *
 * @var Goose $this
 */

try
{
	// check authorization
	$token = Auth::checkAuthorization();

	// set where
	$where = '';
	if ($article = Util::getParameter('article'))
	{
		$where .= ' and article_srl='.$article;
	}
	if ($name = Util::getParameter('name'))
	{
		$where .= ' and name LIKE \'%'.$name.'%\'';
	}
	if ($type = Util::getParameter('type'))
	{
		$where .= ' and type LIKE \'%'.$type.'%\'';
	}
	if ($ready = Util::getParameter('ready'))
	{
		switch ($ready)
		{
			case 'true':
				$where .= ' and ready=1';
				break;
			case 'false':
				$where .= ' and ready=0';
				break;
		}
	}

	// set output
	$output = Controller::index((object)[
		'goose' => $this,
		'table' => 'files',
		'where' => $where
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