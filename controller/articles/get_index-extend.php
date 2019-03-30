<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get articles extend
 * `nest_srl` or `nest_id`를 기준으로 데이터를 가져온다.
 * `nest`, `categories`, `article` 데이터를 한번에 가져올때 사용하기 때문에 자주 사용하지 않는것을 권장한다.
 *
 * # url params
 * - @param int app : app srl
 * - @param int nest : nest srl
 * - @param string nest_id : nest id
 * - @param int category : category srl
 *
 * @var Goose $this
 */

try
{
	// set model
	$model = new Model();
	$model->connect();

	// check authorization
	$token = Auth::checkAuthorization($model);


	// get nest data
	$where = '';
	if ($app = $_GET['app'])
	{
		$where .= ' and app_srl='.$app;
	}
	if ($nest_srl = $_GET['nest'])
	{
		$where .= ' and srl='.$nest_srl;
	}
	else if ($nest_id = $_GET['nest_id'])
	{
		$where .= ' and id LIKE \''.$nest_id.'\'';
	}
	else
	{
		throw new Exception('Not found nest parameter', 404);
	}
	$nest = $model->getItem((object)[
		'table' => 'nests',
		'where' => $where,
		'json_field' => ['json'],
	]);
	if (!($nest && $nest->data)) throw new Exception('Not found nest data', 404);
	$nest = $nest->data;

	// get categories
	$categories = null;
	if (isset($nest->json->useCategory) && (int)$nest->json->useCategory === 1)
	{
		$categories = $model->getItems((object)[
			'table' => 'categories',
			'field' => 'srl,name',
			'where' => 'nest_srl='.(int)$nest->srl,
			'order' => 'turn',
			'sort' => 'asc',
		]);
		$categories = (isset($categories->data) && count($categories->data)) ? $categories->data : [];
		// set extend items
		$categories = \Controller\categories\Util::extendArticleCountInItems($model, $token, $categories);
		$categories = \Controller\categories\Util::extendAllArticlesInItems($model, $token, $categories, $nest->srl);
	}

	// get articles area

	// make where for get articles
	$where = 'nest_srl='.(int)$nest->srl;
	if ($_GET['category'])
	{
		$where .= ($_GET['category'] === 'null') ? ' and category_srl IS NULL' : ' and category_srl='.$_GET['category'];
	}

	// set output
	$output = Controller::index((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'articles',
		'where' => $where,
		'json_field' => ['json'],
		'debug' => true
	]);

	// ext_field - get category name
	if ($output->data && Util::checkKeyInExtField('category_name'))
	{
		$output->data->index = \Controller\articles\Util::extendCategoryNameInItems($model, $output->data->index);
	}
	// ext_field - get next page
	if ($output->data && Util::checkKeyInExtField('next_page'))
	{
		$nextPage = \Controller\articles\Util::getNextPage($this, $model, $where);
		if ($nextPage) $output->data->nextPage = $nextPage;
	}

	// set token
	if ($token) $output->_token = $token->jwt;

	// output
	if ($output->data)
	{
		if ($nest) $output->data->nest = $nest;
		if ($categories) $output->data->categories = $categories;
	}
	Output::data($output);
}
catch (Exception $e)
{
	if (isset($model)) $model->disconnect();
	Error::data($e->getMessage(), $e->getCode());
}
