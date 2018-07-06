<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * edit file
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

	// check authorization
	$token = Auth::checkAuthorization($this->level->admin, $model);

	// remove and upload file
	if ($_FILES['files'] && $_FILES['files']['name'])
	{
		/**
		 * remove file
		 */
		// get item
		$file = $model->getItem((object)[
			'table' => 'file',
			'field' => 'loc',
			'where' => 'srl='.(int)$this->params['srl'],
			'debug' => __DEBUG__,
		]);

		// check exist file
		if (
			isset($file->data->loc) && $file->data->loc &&
			file_exists(__PATH__.'/'.$file->data->loc)
		)
		{
			@unlink(__PATH__.'/'.$file->data->loc);
		}

		/**
		 * upload file
		 */
		// set values
		$file = $_FILES['files'];
		$month = date('Ym');
		$path = 'data/upload/'.(($_POST['sub_dir']) ? $_POST['sub_dir'] : 'original');
		$path_absolute = __PATH__.'/'.$path;
		$path_absolute_dest = $path_absolute.'/'.$month;

		// check path
		if (!is_dir($path_absolute))
		{
			throw new Exception("The directory `/$path/$month` does not exist.");
		}
		if (!is_writable($path_absolute))
		{
			throw new Exception("The `/$path` directory permission is invalid.");
		}

		// make month directory
		File::makeDirectory($path_absolute_dest, 0777);

		// check file
		if ($file['error'])
		{
			throw new Exception($file['error']);
		}

		// check file size
		if ((int)$file['size'] > (int)getenv('FILE_LIMIT_SIZE'))
		{
			throw new Exception('The attachment size exceeds the allowable limit.');
		}

		// check filename
		$file['name'] = File::checkFilename($file['name'], false);
		if (!$file['name'])
		{
			throw new Exception('This file is a format that is not allowed.');
		}

		// check exist filename
		$file['name'] = File::checkExistFile($path_absolute_dest.'/', $file['name'], null);

		// check path and tmp_name
		if (!($file['tmp_name'] && is_dir($path_absolute_dest)))
		{
			throw new Exception('upload error');
		}

		// copy file to target
		move_uploaded_file($file['tmp_name'], $path_absolute_dest.'/'.$file['name']);

		// set loc
		$loc = $path.'/'.$month.'/'.$file['name'];
	}
	else
	{
		$loc = null;
	}

	// check article_srl
	if ($_POST['article_srl'])
	{
		$cnt = $model->getCount((object)[
			'table' => 'article',
			'where' => 'srl='.(int)$_POST['article_srl'],
		]);
		if (!$cnt->data)
		{
			throw new Exception('Not found article data.', 500);
		}
	}

	// TODO: 여기서부터 db 업데이트부터 시작...


}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}