<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * edit draft
 *
 * @var Goose $this
 */

try
{
	$tableName = 'drafts';
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

	// filtering text
	if (isset($_POST['title']))
	{
		$_POST['title'] = addslashes(trim($_POST['title']));
	}
	if (isset($_POST['content']) && $_GET['content'] !== 'raw')
	{
		$_POST['content'] = addslashes($_POST['content']);
	}

	// set output
	$output = Controller::edit((object)[
		'goose' => $this,
		'model' => $model,
		'table' => $tableName,
		'srl' => $srl,
		'data' => [
			$_POST['user_srl'] ? "user_srl='$_POST[user_srl]'" : '',
			$_POST['title'] ? "title='$_POST[title]'" : '',
			$_POST['content'] ? "content='$_POST[content]'" : '',
			$_POST['json'] ? "json='$_POST[json]'" : '',
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
