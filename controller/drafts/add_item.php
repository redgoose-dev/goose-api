<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * add draft
 *
 * @var Goose $this
 */

try
{
	// check post values
	Util::checkExistValue($_POST, [ 'content' ]);

	// set model
	$model = new Model();
	$model->connect();

	// check authorization
	$token = Auth::checkAuthorization($model, 'user');

	// filtering text
	$_POST['title'] = htmlspecialchars(addslashes(trim($_POST['title'])));
	$_POST['title'] = str_replace('&amp;', '&', $_POST['title']);
	$_POST['title'] = str_replace('&quot;', '"', $_POST['title']);
	$_POST['title'] = str_replace('&lt;', '<', $_POST['title']);
	$_POST['title'] = str_replace('&gt;', '>', $_POST['title']);
	if ($_GET['content'] !== 'raw')
	{
		$_POST['content'] = addslashes($_POST['content']);
	}

	// set output
	$output = Controller::add((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'drafts',
		'data' => (object)[
			'srl' => null,
			'user_srl' => (int)$token->data->user_srl,
			'title' => $_POST['title'],
			'content' => $_POST['content'],
			'json' => $_POST['json'],
			'description' => $_POST['description'],
			'regdate' => date('YmdHis'),
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
	if (isset($model)) $model->disconnect();
	Error::data($e->getMessage(), $e->getCode());
}
