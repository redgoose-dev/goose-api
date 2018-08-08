<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * edit article
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

	// set model
	$model = new Model();
	$model->connect();

	// check access
	$token = Controller::checkAccessItem((object)[
		'model' => $model,
		'table' => $tableName,
		'srl' => $srl,
	]);

	// filtering text
	if (isset($_POST['title']))
	{
		$_POST['title'] = htmlspecialchars(addslashes(trim($_POST['title'])));
	}
	if (isset($_POST['content']))
	{
		// TODO: `content`에서 `json`으로 들어오는 경우에 문제가 일어날 수 있기 때문에 사용에 옵션을 둬야할 수 있음.
		$_POST['content'] = addslashes($_POST['content']);
	}

	// set values
	if (isset($_POST['category_srl']))
	{
		$category_srl = ($_POST['category_srl']) ? "'$_POST[category_srl]'" : 'NULL';
	}

	// set output
	$output = Controller::edit((object)[
		'goose' => $this,
		'model' => $model,
		'table' => $tableName,
		'srl' => $srl,
		'data' => [
			$_POST['app_srl'] ? "app_srl='$_POST[app_srl]'" : '',
			isset($_POST['nest_srl']) ? "nest_srl='$_POST[nest_srl]'" : '',
			isset($category_srl) ? "category_srl=$category_srl" : '',
			$_POST['user_srl'] ? "user_srl='$_POST[user_srl]'" : '',
			$_POST['title'] ? "title='$_POST[title]'" : '',
			$_POST['content'] ? "content='$_POST[content]'" : '',
			$_POST['hit'] ? "hit='$_POST[hit]'" : '',
			$_POST['json'] ? "json='$_POST[json]'" : '',
			"modate='".date("YmdHis")."'"
		],
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
