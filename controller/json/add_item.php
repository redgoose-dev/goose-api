<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * add json
 *
 * @var Goose $this
 */

try
{
	// check post values
	Util::checkExistValue($_POST, [ 'name', 'json' ]);

	// set json
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

	// set output
	try
	{
		$output = Controller\Main::add((object)[
			'model' => $this->model,
			'table' => 'json',
			'data' => (object)[
				'srl' => null,
				'user_srl' => $token->data->user_srl,
				'name' => $_POST['name'],
				'description' => $_POST['description'],
				'json' => $json,
				'regdate' => date('Y-m-d H:i:s'),
			]
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
	Output::data($output);
}
catch (Exception $e)
{
  $this->model->disconnect();
	Error::data($e->getMessage(), $e->getCode());
}
