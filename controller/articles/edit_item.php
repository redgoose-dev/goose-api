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
		throw new Exception('Not found srl', 500);
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

	// set output
	$output = Controller::edit((object)[
		'goose' => $this,
		'model' => $model,
		'table' => $tableName,
		'srl' => $srl,
		'data' => [
			isset($_POST['app_srl']) ? "app_srl='$_POST[app_srl]'" : '',
			isset($_POST['nest_srl']) ? "nest_srl='$_POST[nest_srl]'" : '',
			isset($_POST['category_srl']) ? "category_srl='$_POST[category_srl]'" : '',
			isset($_POST['user_srl']) ? "user_srl='$_POST[user_srl]'" : '',
			isset($_POST['title']) ? "title='$_POST[title]'" : '',
			isset($_POST['content']) ? "content='$_POST[content]'" : '',
			isset($_POST['hit']) ? "hit='$_POST[hit]'" : '',
			isset($_POST['json']) ? "json='$_POST[json]'" : '',
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
