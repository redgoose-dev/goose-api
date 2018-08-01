<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get app
 *
 * @var Goose $this
 */

try
{
	// check srl
	if (!((int)$this->params['srl'] && $this->params['srl'] > 0))
	{
		throw new Exception('Not found srl', 204);
	}

	// set model
	$model = new Model();
	$model->connect();

	// check access
	if ($_GET['strict'])
	{
		$token = Auth::checkAuthorization($model, 'user');

		if (!$token->data->admin)
		{
			// check user srl
			if ((int)$token->data->user_srl !== (int)$this->params['srl'])
			{
				throw new Exception('It is not your data.', 401);
			}
		}
	}
	else
	{
		$token = Auth::checkAuthorization($model);
	}

	// set output
	$output = Controller::item((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'app',
		'srl' => (int)$this->params['srl'],
	]);

	// set token
	if ($token) $output->_token = $token->jwt;

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
