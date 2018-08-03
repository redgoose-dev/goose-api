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
	$tableName = 'categories';
	$srl = (int)$this->params['srl'];

	// check srl
	if (!($srl && $srl > 0))
	{
		throw new Exception('Not found srl', 500);
	}

	// set model
	$model = new Model();
	$model->connect();

	// check access
	$token = Controller::checkAccessItem((object)[
		'model' => $model,
		'table' => $tableName,
		'srl' => $srl,
		'useStrict' => true,
	]);

	// set output
	$output = Controller::item((object)[
		'goose' => $this,
		'model' => $model,
		'table' => $tableName,
		'srl' => $srl,
	]);

	// get article count
	if ($output->data && Util::checkKeyInExtField('count_article'))
	{
		$cnt = $model->getCount((object)[
			'table' => 'articles',
			'where' => 'category_srl='.(int)$output->data->srl,
		]);
		$output->data->count_article = $cnt->data;
	}

	// set token
	if ($token) $output->_token = $token->jwt;

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
