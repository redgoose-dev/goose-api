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

	// check order date
	if ($_POST['order'] && !preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $_POST['order']))
	{
		throw new Exception('Error order date', 500);
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

	// set values
	$category_srl = ($_POST['category_srl']) ? "'$_POST[category_srl]'" : 'NULL';

	// set output
	$output = Controller::edit((object)[
		'goose' => $this,
		'model' => $model,
		'table' => $tableName,
		'srl' => $srl,
		'data' => [
			$_POST['app_srl'] ? "`app_srl`='$_POST[app_srl]'" : '',
			isset($_POST['nest_srl']) ? "`nest_srl`='$_POST[nest_srl]'" : '',
			isset($category_srl) ? "`category_srl`=$category_srl" : '',
			$_POST['user_srl'] ? "`user_srl`='$_POST[user_srl]'" : '',
			$_POST['type'] ? "`type`='$_POST[type]'" : 'type=NULL',
			$_POST['title'] ? "`title`='$_POST[title]'" : '',
			$_POST['content'] ? "`content`='$_POST[content]'" : '',
			$_POST['hit'] ? "`hit`='$_POST[hit]'" : '',
			$_POST['star'] ? "`star`='$_POST[star]'" : '',
			$_POST['json'] ? "`json`='$_POST[json]'" : '',
			"`modate`='".date("Y-m-d H:i:s")."'",
			"`order`='".($_POST['order'] ? date('Y-m-d', strtotime($_POST['order'])) : date('Y-m-d'))."'",
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
	if (isset($model)) $model->disconnect();
	Error::data($e->getMessage(), $e->getCode());
}
