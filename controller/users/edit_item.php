<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * edit user
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

	// check authorization
	$token = Auth::checkAuthorization();

	// set model
	$model = new Model();
	$model->connect();

	// set user value
	$user_token = Token::get(__TOKEN__)->data;
	$user_db = $model->getItem((object)[
		'table' => 'user',
		'field' => 'srl,level',
		'where' => 'srl='.(int)$this->params['srl'],
	])->data;

	// not admin
	if ($this->level->admin > (int)$user_db->level)
	{
		// if not self
		if ((int)$user_token->user_srl !== (int)$user_db->srl)
		{
			throw new Exception('Error user level');
		}
		// blank level
		$_POST['level'] = null;
	}

	// check email address
	if ($_POST['email'])
	{
		$cnt = $model->getCount((object)[
			'table' => 'user',
			'where' => 'email="'.$_POST['email'].'"',
			'debug' => __DEBUG__
		]);
		if (isset($cnt->data) && $cnt->data > 0)
		{
			throw new Exception('The email address already exists.', 500);
		}
	}

	// set output
	$output = Controller::edit((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'user',
		'srl' => (int)$this->params['srl'],
		'data' => [
			$_POST['email'] ? "email='$_POST[email]'" : '',
			$_POST['name'] ? "name='$_POST[name]'" : '',
			$_POST['level'] ? "level='$_POST[level]'" : '',
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