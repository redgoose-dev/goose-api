<?php
namespace Core;
use Exception;

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

try
{
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
		$where .= ($category === 'null') ? ' and category_srl IS NULL' : ' and category_srl='.$category;
	}
	if ($title = Util::getParameter('title'))
	{
		$where .= ' and title LIKE \'%'.$title.'%\'';
	}
	if ($content = Util::getParameter('content'))
	{
		$where .= ' and content LIKE \'%'.$content.'%\'';
	}

	// set model
	$model = new Model();
	$model->connect();

	// check access
	$token = Controller::checkAccessIndex($model, true);
	$where .= (!$token->data->admin && $token->data->user_srl) ? ' and user_srl='.(int)$token->data->user_srl : '';

	// set output
	$output = Controller::index((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'articles',
		'where' => $where,
		'json_field' => ['json']
	]);

	// get category name
	if ($output->data && Util::checkKeyInExtField('category_name'))
	{
		$output->data->index = \Controller\articles\Util::extendCategoryNameInItems($model, $output->data->index);
	}

	// get next page
	if ($output->data && Util::checkKeyInExtField('next_page'))
	{
		$nextPage = \Controller\articles\Util::getNextPage($this, $model, $where);
		if ($nextPage) $output->data->nextPage = $nextPage;
	}

	// set token
	if ($token) $output->_token = $token->jwt;

	// output
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
