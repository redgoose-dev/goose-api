<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get article
 *
 * url params
 * - @param int srl
 * - @param int hit 조회수 증가 사용
 * - @param string ext_field `category_name,nest_name`
 *
 * @var Goose $this
 */

try
{
	$tableName = 'articles';
	$srl = (int)$this->params['srl'];

	// check srl
	if (!($srl && $srl > 0))
	{
		throw new Exception('Not found srl', 204);
	}

	// set where
	$where = '';
	if ($app = $_GET['app'])
	{
		$where .= ' and app_srl='.$app;
	}
	if ($nest = $_GET['nest'])
	{
		$where .= ' and nest_srl='.$nest;
	}
	if ($category = $_GET['category'])
	{
		$where .= ($category === 'null') ? ' and category_srl IS NULL' : ' and category_srl='.$category;
	}
	if ($_GET['visible_type'] === 'all')
	{}
	else
	{
		$where .= ' and type IS NULL'; // type 필드가 `null`일때 공개된 글입니다.
	}

	// set model
	$model = new Model();
	$model->connect();

	// check access
	$token = Controller::checkAccessItem((object)[
		'model' => $model,
		'table' => $tableName,
		'srl' => $srl,
		'useStrict' => true,
	]);

	// set output
	$output = Controller::item((object)[
		'goose' => $this,
		'model' => $model,
		'table' => $tableName,
		'srl' => $srl,
		'where' => $where,
		'json_field' => ['json'],
	]);

	// get category name
	if ($output->data && $output->data->category_srl && Util::checkKeyInExtField('category_name'))
	{
		$category = $model->getItem((object)[
			'table' => 'categories',
			'field' => 'name',
			'where' => 'srl='.(int)$output->data->category_srl,
		]);
		if ($category->data && $category->data->name)
		{
			$output->data->category_name = $category->data->name;
		}
	}

	// get nest name
	if ($output->data && $output->data->nest_srl && Util::checkKeyInExtField('nest_name'))
	{
		$nest = $model->getItem((object)[
			'table' => 'nests',
			'where' => 'srl='.(int)$output->data->nest_srl,
		]);
		if (isset($nest->data->name))
		{
			$output->data->nest_name = $nest->data->name;
		}
	}

	// update hit
	if ($_GET['hit'] && isset($output->data->hit))
	{
		$output->data->hit = $output->data->hit + 1;
		$hit = (int)$output->data->hit;
		$model->edit((object)[
			'table' => $tableName,
			'where' => 'srl='.$srl,
			'data' => [ "hit='$hit'" ]
		]);
	}

	// set token
	if ($token) $output->_token = $token->jwt;

	// disconnect db
	$model->disconnect();

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	if (isset($model)) $model->disconnect();
	Error::data($e->getMessage(), $e->getCode());
}
