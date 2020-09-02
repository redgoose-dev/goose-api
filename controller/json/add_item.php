<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * add json
 *
 * @var Goose|Connect $this
 */

try
{
	// check post values
	Util::checkExistValue($this->post, [ 'name', 'json' ]);

	// set json
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

	// set output
	try
	{
		$output = Controller\Main::add($this, (object)[
			'table' => 'json',
			'data' => (object)[
				'srl' => null,
				'user_srl' => $token->data->user_srl,
				'name' => $this->post->name,
				'description' => $this->post->description,
				'json' => $json,
				'regdate' => date('Y-m-d H:i:s'),
			],
		]);
	}
	catch(Exception $e)
	{
    throw new Exception(Message::make('error.failedAdd', 'json'));
	}

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
