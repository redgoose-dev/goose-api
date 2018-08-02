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
	// check authorization
	$token = Auth::checkAuthorization();

	// set model
	$model = new Model();
	$model->connect();

	// set where
	$where = '';
	if ($nest = (int)Util::getParameter('nest'))
	{
		$where .= ' and nest_srl='.$nest;
	}
	if ($name = Util::getParameter('name'))
	{
		$where .= ' and name LIKE \'%'.$name.'%\'';
	}

	// set output
	$output = Controller::index((object)[
		'model' => $model,
		'goose' => $this,
		'table' => 'categories',
		'where' => $where
	]);

	if ($output->data)
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
				'srl' => 0,
				'nest_srl' => $nest,
				'name' => 'All',
			];
			// get article count
			if (Util::checkKeyInExtField('count_article'))
			{
				$cnt = $model->getCount((object)[
					'table' => 'articles',
					'where' => $nest ? 'nest_srl='.$nest : null,
				]);
				$item->count_article = $cnt->data;
			}

			// add item
			array_unshift($output->data->index, $item);
		}
	}

	// set token
	if ($token) $output->_token = $token;

	// output
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}