<?php
namespace Core;
use Controller\Main;
use Exception;

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
	if (!Text::allowString($this->post->id ?? '', null))
	{
    throw new Exception(Message::make('error.onlyKeywordType', 'id'));
	}

  // set json
  if ($json = $this->post->json ?? null) $json = Util::testJsonData($json);

  // connect db
  $this->model->connect();

	// check authorization
	$token = Auth::checkAuthorization($this->model, 'user');

	// check app
	$cnt = $this->model->getCount((object)[
		'table' => 'apps',
		'where' => 'srl='.(int)$this->post->app_srl,
	])->data;
	if ($cnt <= 0)
	{
		throw new Exception(Message::make('error.noData', 'apps'));
	}

	// check duplicate nest id
	$cnt = $this->model->getCount((object)[
		'table' => 'nests',
		'where' => 'id="'.trim($this->post->id).'"',
	])->data;
	if ($cnt > 0)
	{
		throw new Exception(Message::make('error.duplicate', 'id'));
	}

	// set output
	$output = Main::add($this, (object)[
		'table' => 'nests',
		'data' => (object)[
			'srl' => null,
			'app_srl' => $this->post->app_srl,
			'user_srl' => (int)$token->data->srl,
			'id' => trim($this->post->id ?? ''),
			'name' => trim($this->post->name ?? ''),
			'description' => trim($this->post->description ?? ''),
			'json' => $json,
			'regdate' => date('Y-m-d H:i:s'),
		]
	]);

	// set token
	if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

	// output data
	return Output::result($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
