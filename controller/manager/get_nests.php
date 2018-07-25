<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * manager / nests
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

	// make tree
	// get apps
	$apps = $model->getItems((object)[
		'table' => 'app',
		'field' => 'srl,name'
	]);
	if (!isset($apps->data))
	{
		throw new Exception('Not found apps.', 404);
	}
	$tree = [];
	foreach ($apps->data as $k=>$v)
	{
		$nest = $model->getItems((object)[
			'table' => 'nest',
			'field' => 'srl,app_srl',
			'where' => 'app_srl='.(int)$v->srl,
			'json_field' => ['json']
		]);
		$tree[] = (object)[
			'name' => $v->name,
			'children' => $nest->data
		];
	}
	// TODO: 마지막은 app_srl이 없는 nest를 찾아 붙이기
	print_r($tree);

//	// make tree
//	// get nests
//	$nests = $model->getItems((object)[
//		'table' => 'nest',
//		'json_field' => ['json']
//	]);
//	$tree = [];
//	foreach ($nests->data as $k=>$v)
//	{
//		$app_srl = $v['app_srl'] ? $v['app_srl'] : 'NULL';
//		if (!isset($tree[$app_srl]))
//		{
//			$tree[$app_srl] = (object)[ 'children' => [] ];
//		}
//		$tree[$app_srl]->children[] = $v;
//
//		// get app
//		if ($app_srl === 'NULL')
//		{
//			$tree[$app_srl]->name = '';
//		}
//		else
//		{
//			$app = $model->getItem((object)[
//				'table' => 'app',
//				'field' => 'name',
//				'where' => 'srl='.(int)$app_srl
//			]);
//			$tree[$app_srl]->name = $app->data->name;
//		}
//	}
//	ksort($tree);

}
catch(Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
