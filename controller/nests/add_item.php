<?php
namespace Core;
use Exception, Controller;

if (!defined('__GOOSE__')) exit();

/**
 * add nest
 *
 * @var Goose $this
 */

try
{
	// check post values
	Util::checkExistValue($_POST, [ 'app_srl', 'id', 'name' ]);

	// check `id`
	if (!Text::allowString($_POST['id']))
	{
    throw new Exception(Message::make('error.onlyKeywordType', 'id'));
	}

	// check and set json
	$json = null;
	if (isset($_POST['json']))
	{
		$json = json_decode(urldecode($_POST['json']), false);
		if (!$json)
		{
			throw new Exception(Message::make('error.json'));
		}
		$json = urlencode(json_encode($json, false));
	}

  // connect db
  $this->model->connect();

	// check authorization
	$token = Auth::checkAuthorization($this->model, 'user');

	// check app
	$cnt = $this->model->getCount((object)[
		'table' => 'apps',
		'where' => 'srl='.(int)$_POST['app_srl'],
	]);
	if (!$cnt->data)
	{
		throw new Exception(Message::make('error.noData', 'apps'));
	}

	// check duplicate nest id
	$cnt = $this->model->getCount((object)[
		'table' => 'nests',
		'where' => 'id="'.trim($_POST['id']).'"',
	]);
	if ($cnt->data)
	{
		throw new Exception(Message::make('error.duplicate', 'id'));
	}

	// set output
	$output = Controller\Main::add((object)[
		'model' => $this->model,
		'table' => 'nests',
		'data' => (object)[
			'srl' => null,
			'app_srl' => $_POST['app_srl'],
			'user_srl' => (int)$token->data->user_srl,
			'id' => trim($_POST['id']),
			'name' => trim($_POST['name']),
			'description' => trim($_POST['description']),
			'json' => $json,
			'regdate' => date('Y-m-d H:i:s'),
		]
	]);

	// set token
	if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

	// output data
	Output::data($output);
}
catch (Exception $e)
{
  $this->model->disconnect();
	Error::data($e->getMessage(), $e->getCode());
}
