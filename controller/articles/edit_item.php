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
	// check srl
	if (!((int)$this->params['srl'] && $this->params['srl'] > 0))
	{
		throw new Exception('Not found srl', 500);
	}

	// set model
	$model = new Model();
	$model->connect();

	// check authorization
	$token = Auth::checkAuthorization($this->level->admin, $model);

	// set output
	$output = Controller::edit((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'articles',
		'srl' => (int)$this->params['srl'],
		'data' => [
			$_POST['app_srl'] ? "app_srl='$_POST[app_srl]'" : '',
			$_POST['nest_srl'] ? "nest_srl='$_POST[nest_srl]'" : '',
			$_POST['category_srl'] ? "category_srl='$_POST[category_srl]'" : '',
			$_POST['user_srl'] ? "user_srl='$_POST[user_srl]'" : '',
			$_POST['title'] ? "title='$_POST[title]'" : '',
			$_POST['content'] ? "content='$_POST[content]'" : '',
			$_POST['hit'] ? "hit='$_POST[hit]'" : '',
			$_POST['json'] ? "json='$_POST[json]'" : '',
			"modate='".date("YmdHis")."'"
		],
	]);

	// set token
	if ($token) $output->_token = $token;

	// disconnect db
	$model->disconnect();

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
