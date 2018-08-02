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
			// get user_srl
			$app = $model->getItem((object)[
				'table' => 'apps',
				'field' => 'user_srl',
				'where' => 'srl='.(int)$this->params['srl'],
			]);
			$user_srl = $app->data ? $app->data->user_srl : null;

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
		'table' => 'apps',
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
