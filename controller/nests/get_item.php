<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get nest
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

	// set model
	$model = new Model();
	$model->connect();

	// check access
	if ($_GET['strict'])
	{
		$token = Auth::checkAuthorization($model, 'user');

		if (!$token->data->admin)
		{
			// get user_srl
			$nest = $model->getItem((object)[
				'table' => 'nests',
				'field' => 'user_srl',
				'where' => 'srl='.(int)$this->params['srl'],
			]);
			$user_srl = $nest->data ? $nest->data->user_srl : null;

			// check user srl
			if ((int)$token->data->user_srl !== (int)$user_srl)
			{
				throw new Exception('You can not access data.', 401);
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
		'table' => 'nests',
		'srl' => (int)$this->params['srl'],
		'json_field' => ['json'],
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
