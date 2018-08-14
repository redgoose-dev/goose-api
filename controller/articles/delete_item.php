<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * delete article
 *
 * @var Goose $this
 */

try
{
	$tableName = 'articles';
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

	// remove thumbnail image
	$article = $model->getItem((object)[
		'table' => 'articles',
		'where' => 'srl='.$srl
	]);
	var_dump($article);
	// TODO: 썸네일 이미지 삭제
	exit;

	// remove files
	$files = $model->getItems((object)[
		'table' => 'files',
		'where' => 'article_srl='.$srl,
	]);
	if ($files->data && count($files->data))
	{
		foreach ($files->data as $k=>$v)
		{
			if (isset($v->loc) && $v->loc && file_exists(__PATH__.'/'.$v->loc))
			{
				unlink(__PATH__.'/'.$v->loc);
			}
		}
		// remove db
		$model->delete((object)[
			'table' => 'files',
			'where' => 'article_srl='.$srl
		]);
	}

	// remove item
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
