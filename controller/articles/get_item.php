<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get article
 *
 * url params
 * - @param int hit 조회수 증가 사용
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

	// set output
	$output = Controller::item((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'article',
		'jsonField' => ['json'],
		'srl' => (int)$this->params['srl'],
	]);

	// update hit
	if ($_GET['hit'] && isset($output->data->hit))
	{
		$hit = (int)$output->data->hit + 1;
		$model->edit((object)[
			'table' => 'article',
			'where' => 'srl='.(int)$this->params['srl'],
			'data' => [ "hit='$hit'" ]
		]);
	}

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
