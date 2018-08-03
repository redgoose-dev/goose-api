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
	// set model
	$model = new Model();
	$model->connect();

	// check authorization
	$token = Auth::checkAuthorization($model, 'user');

	// get apps
	$apps = $model->getItems((object)[ 'table' => 'apps' ]);
	if (!isset($apps->data))
	{
		throw new Exception('Not found apps.', 404);
	}
	$tree = [];
	foreach ($apps->data as $k=>$v)
	{
		$nests = $model->getItems((object)[
			'table' => 'nests',
			'where' => 'app_srl='.(int)$v->srl,
			'json_field' => ['json'],
		]);
		$tree[] = (object)[
			'srl' => (int)$v->srl,
			'name' => $v->name,
			'description' => $v->description,
			'count' => count($nests->data),
			'children' => $nests->data,
		];
	}

	// add no app
	$nests = $model->getItems((object)[
		'table' => 'nests',
		'where' => 'app_srl IS NULL',
		'json_field' => ['json'],
	]);
	if (isset($nests->data) && $nests->data)
	{
		$tree[] = (object)[
			'srl' => null,
			'name' => null,
			'description' => null,
			'count' => count($nests->data),
			'children' => $nests->data,
		];
	}

	// set output
	$output = (object)[];
	$output->code = count($tree) ? 200 : 404;
	if (count($tree)) $output->data = $tree;

	// set token
	if ($token) $output->_token = $token->jwt;

	// output
	Output::data($output);
}
catch(Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
