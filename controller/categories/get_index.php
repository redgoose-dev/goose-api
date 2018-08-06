<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get categories
 *
 * url params
 * - @param int nest
 * - @param string name
 *
 * @var Goose $this
 */

try
{
	// set model
	$model = new Model();
	$model->connect();

	// set where
	$where = '';
	if ($nest = (int)$_GET['nest'])
	{
		$where .= ' and nest_srl='.$nest;
	}
	if ($name = $_GET['name'])
	{
		$where .= ' and name LIKE \'%'.$name.'%\'';
	}

	// check access
	$token = Controller::checkAccessIndex($model, true);
	$where .= (!$token->data->admin) ? ' and user_srl='.(int)$token->data->user_srl : '';

	// set output
	$output = Controller::index((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'categories',
		'where' => $where
	]);

	if ($output->data && isset($_GET['ext_field']))
	{
		// get article count
		if (Util::checkKeyInExtField('count_article'))
		{
			foreach ($output->data->index as $k=>$v)
			{
				$cnt = $model->getCount((object)[
					'table' => 'articles',
					'where' => 'category_srl='.(int)$v->srl,
				]);
				$output->data->index[$k]->count_article = $cnt->data;
			}
		}

		// get all item
		if (Util::checkKeyInExtField('item_all'))
		{
			// set item
			$item = (object)[
				'srl' => '',
				'nest_srl' => $nest,
				'name' => 'All',
			];
			// get article count
			if (Util::checkKeyInExtField('count_article'))
			{
				$where = $nest ? 'nest_srl='.$nest : '';
				$where .= (!$token->data->admin) ? ' and user_srl='.(int)$token->data->user_srl : '';
				$cnt = $model->getCount((object)[
					'table' => 'articles',
					'where' => $where,
				]);
				$item->count_article = $cnt->data;
			}
			// add item
			array_unshift($output->data->index, $item);
		}

		// get un category
		if (Util::checkKeyInExtField('none') && Util::checkKeyInExtField('count_article'))
		{
			// set item
			$item = (object)[
				'srl' => 'null',
				'nest_srl' => $nest,
				'name' => 'none',
			];
			$where = $nest ? 'nest_srl='.$nest : '';
			$where .= (!$token->data->admin) ? ' and user_srl='.(int)$token->data->user_srl : '';
			$where .= ' and category_srl IS NULL';
			$cnt = $model->getCount((object)[
				'table' => 'articles',
				'where' => $where,
			]);
			$item->count_article = $cnt->data;
			// add item
			array_push($output->data->index, $item);
		}
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