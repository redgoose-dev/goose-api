<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * add nest
 *
 * @var Goose|Connect $this
 */

try
{
	// check post values
	Util::checkExistValue($this->post, [ 'app_srl', 'id', 'name' ]);

	// check `id`
	if (!Text::allowString($this->post->id))
	{
    throw new Exception(Message::make('error.onlyKeywordType', 'id'));
	}

	// check and set json
	$json = null;
	if (isset($this->post->json))
	{
		$json = json_decode(urldecode($this->post->json), false);
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
		'where' => 'srl='.(int)$this->post->app_srl,
	]);
	if (!$cnt->data)
	{
		throw new Exception(Message::make('error.noData', 'apps'));
	}

	// check duplicate nest id
	$cnt = $this->model->getCount((object)[
		'table' => 'nests',
		'where' => 'id="'.trim($this->post->id).'"',
	]);
	if ($cnt->data)
	{
		throw new Exception(Message::make('error.duplicate', 'id'));
	}

	// set output
	$output = Controller\Main::add($this, (object)[
		'table' => 'nests',
		'data' => (object)[
			'srl' => null,
			'app_srl' => $this->post->app_srl,
			'user_srl' => (int)$token->data->user_srl,
			'id' => trim($this->post->id),
			'name' => trim($this->post->name),
			'description' => trim($this->post->description),
			'json' => $json,
			'regdate' => date('Y-m-d H:i:s'),
		]
	]);

	// set token
	if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

	// output data
	return Output::data($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::data($e->getMessage(), $e->getCode());
}
