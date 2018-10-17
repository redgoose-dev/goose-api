<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * update hit or star from article
 *
 * @var Goose $this
 */

try
{
	$tableName = 'articles';
	$srl = (int)$this->params['srl'];
	$type = $_GET['type'];

	// check srl
	if (!($srl && $srl > 0))
	{
		throw new Exception('Not found srl', 500);
	}
	// check type
	if (!($type === 'hit' || $type === 'star'))
	{
		throw new Exception('Not found type', 500);
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

	// get article
	$article = $model->getItem((object)[
		'table' => $tableName,
		'field' => 'srl,hit,star',
		'where' => 'srl='.$srl,
		'debug' => true,
	]);

	$data = [];
	switch ($type)
	{
		case 'hit':
			$data[] = 'hit='.((int)$article->data->hit + 1);
			break;
		case 'star':
			$data[] = 'star='.((int)$article->data->star + 1);
			break;
	}

	// set output
	$output = Controller::edit((object)[
		'goose' => $this,
		'model' => $model,
		'table' => $tableName,
		'srl' => $srl,
		'data' => $data,
	]);

	switch ($type)
	{
		case 'hit':
			$output->data = (object)[ 'hit' => (int)$article->data->hit + 1 ];
			break;
		case 'star':
			$output->data = (object)[ 'star' => (int)$article->data->star + 1 ];
			break;
	}

	// set token
	if ($token) $output->_token = $token->jwt;

	// disconnect db
	$model->disconnect();

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	if (isset($model)) $model->disconnect();
	Error::data($e->getMessage(), $e->getCode());
}
