<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * delete nest
 *
 * @var Goose $this
 */

try
{
	$tableName = 'nests';
	$srl = (int)$this->params['srl'];

	// check srl
	if (!($srl && $srl > 0))
	{
		throw new Exception('Not found srl', 204);
	}

	// set model
	$model = new Model();
	$model->connect();

	// check access
	$token = Controller::checkAccessItem((object)[
		'model' => $model,
		'table' => $tableName,
		'srl' => $srl,
	]);

	// articles
	$articles = $model->getItems((object)[
		'table' => 'articles',
		'field' => 'srl',
		'where' => 'nest_srl='.$srl,
	]);
	if ($articles->data && count($articles->data))
	{
		foreach($articles->data as $k=>$v)
		{
			// remove thumbnail image
			Controller::removeThumbnailImage($model, $v->srl);

			// remove files
			Controller::removeAttachFiles($model, $v->srl);
		}
		// remove articles
		$model->delete((object)[
			'table' => 'articles',
			'where' => 'nest_srl='.$srl
		]);
	}

	// categories
	$categoriesCount = $model->getCount((object)[
		'table' => 'categories',
		'where' => 'nest_srl='.$srl,
	]);
	if ($categoriesCount->data > 0)
	{
		// remove categories
		$model->delete((object)[
			'table' => 'categories',
			'where' => 'nest_srl='.$srl
		]);
	}

	// remove nest
	$output = Controller::delete((object)[
		'goose' => $this,
		'model' => $model,
		'table' => $tableName,
		'srl' => $srl,
	]);

	// set output
	if ($token) $output->_token = $token->jwt;

	// disconnect db
	$model->disconnect();

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
