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
 * - @param string keyword
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
	if ($keyword = Util::getParameter('keyword'))
	{
		$where .= ' and (title LIKE \'%'.$keyword.'%\' or content LIKE \'%'.$keyword.'%\')';
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

	// get page navigation
	if ($output->data && Util::checkKeyInExtField('page_navigation'))
	{
		// get total count
		$count = $model->getCount((object)[
			'table' => 'articles',
			'where' => $where,
		]);
		if ($count->data && $count->data > 0)
		{
			// set values
			$params = [ 'keyword' => ($_GET['keyword']) ? $_GET['keyword'] : '' ];
			$page = ($_GET['page']) ? (int)$_GET['page'] : 1;
			$size = ($_GET['size']) ? (int)$_GET['size'] : 10;
			$scale = ($_GET['size']) ? (int)$_GET['scale'] : 10;
			// set paginate
			$paginate = new Paginate($count->data, $page, $params, $size, $scale);
			// make navigation object
			$pageNavigation = $paginate->createNavigationToObject();
			// set output data
			$output->data->navigation = $pageNavigation ? $pageNavigation : null;
		}
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
