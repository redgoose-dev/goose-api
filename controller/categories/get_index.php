<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get apps
 *
 * @var Goose $this
 *
 * // URL params
 * @param string field
 * @param string order
 * @param string sort
 * @param string limit
 * @param int page
 * @param int size
 *
 * @param int nest
 * @param string name
 */

try
{
	// get values
	$output = (object)[];
	$model = new Model();
	$page = (($_GET['page']) ? (int)$_GET['page'] : $this->defaults->page) - 1;
	$size = ($_GET['size']) ? (int)$_GET['size'] : $this->defaults->size;

	// connect db
	$tmp = $model->connect();
	if ($tmp)
	{
		throw new Exception($tmp->getMessage(), $tmp->getCode());
	}

	// set where
	$where = '';
	if ($nest = Util::getParameter('nest'))
	{
		$where .= ' and nest_srl='.$nest;
	}
	if ($name = Util::getParameter('name'))
	{
		$where .= ' and name LIKE \'%'.$name.'%\'';
	}

	// get total
	$total = $model->getCount((object)[
		'table' => 'category',
		'where' => $where,
		'debug' => __DEBUG__
	]);

	// set limit
	$limit = null;
	if (isset($_GET['limit']))
	{
		$limit = explode(',', $_GET['limit']);
	}
	else if (isset($_GET['page']) || isset($_GET['size']))
	{
		$limit = [ $page * $size, $size ];
	}

	// get datas
	$items = $model->getItems((object)[
		'table' => 'category',
		'field' => $_GET['field'],
		'order' => $_GET['order'],
		'sort' => $_GET['sort'],
		'limit' => $limit,
		'where' => $where,
		'debug' => __DEBUG__
	]);

	// disconnect db
	$model->disconnect();

	// set output
	$output->code = 200;
	$output->data = $items->data;
	if (isset($total->data)) $output->total = $total->data;
	if ($items->query) $output->query = $items->query;

	// output data
	Output::json($output, $_GET['min']);
}
catch (Exception $e)
{
	Output::json((object)[
		'code' => $e->getCode(),
		'message' => $e->getMessage()
	]);
}
