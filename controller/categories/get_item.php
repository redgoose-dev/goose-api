<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get category
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
		'model' => $model,
		'goose' => $this,
		'table' => 'category',
		'srl' => (int)$this->params['srl'],
	]);

	// get article count
	if ($output->data && Util::getParameter('count_article'))
	{
		$cnt = $model->getCount((object)[
			'table' => 'article',
			'where' => 'category_srl='.(int)$output->data->srl,
		]);
		$output->data->count_article = $cnt->data;
	}

	// set token
	if ($token) $output->_token = $token;

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
