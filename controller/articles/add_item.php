<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * add article
 *
 * @var Goose $this
 */

try
{
	// check post values
	Util::checkExistValue($_POST, [ 'app_srl', 'nest_srl', 'title', 'content' ]);

	// set model
	$model = new Model();
	$model->connect();

	// check authorization
	$token = Auth::checkAuthorization($model, 'user');

	// filtering text
	$_POST['title'] = htmlspecialchars(addslashes(trim($_POST['title'])));
	// TODO: `content`에서 `json`으로 들어오는 경우에 문제가 일어날 수 있기 때문에 사용에 옵션을 둬야할 수 있음.
	$_POST['content'] = addslashes($_POST['content']);

	// check nest
	$cnt = $model->getCount((object)[
		'table' => 'nests',
		'where' => 'srl='.(int)$_POST['nest_srl'],
	]);
	if (!$cnt->data)
	{
		throw new Exception('There is no `nests` data.', 204);
	}

	// check app
	$cnt = $model->getCount((object)[
		'table' => 'apps',
		'where' => 'srl='.(int)$_POST['app_srl'],
	]);
	if (!$cnt->data)
	{
		throw new Exception('There is no `apps` data.', 204);
	}

	// check category
	if ($_POST['category_srl'] && (int)$_POST['category_srl'] > 0)
	{
		$cnt = $model->getCount((object)[
			'table' => 'categories',
			'where' => 'srl='.(int)$_POST['category_srl'],
		]);
		if (!$cnt->data)
		{
			throw new Exception('There is no `categories` data.', 204);
		}
	}

	// set output
	$output = Controller::add((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'articles',
		'data' => (object)[
			'srl' => null,
			'app_srl' => $_POST['app_srl'],
			'nest_srl' => $_POST['nest_srl'],
			'category_srl' => $_POST['category_srl'],
			'user_srl' => (int)$token->data->user_srl,
			'title' => $_POST['title'],
			'content' => $_POST['content'],
			'hit' => 0,
			'json' => $_POST['json'],
			'ip' => ($_SERVER['REMOTE_ADDR'] !== '::1') ? $_SERVER['REMOTE_ADDR'] : 'localhost',
			'regdate' => date('YmdHis'),
			'modate' => date('YmdHis'),
		]
	]);

	// set token
	if ($token) $output->_token = $token->jwt;

	// disconnect db
	$model->disconnect();

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}