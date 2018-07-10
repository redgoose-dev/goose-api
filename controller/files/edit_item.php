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
		$subDir = ($_POST['sub_dir']) ? $_POST['sub_dir'] : getenv('DEFAULT_UPLOAD_DIR_NAME');

		// set path
		$path = 'data/upload/'.$subDir;
		$path_absolute = __PATH__.'/'.$path;
		$path_absolute_dest = $path_absolute.'/'.$month;

		// make sub directory
		File::makeDirectory($path_absolute, 0707);

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

		// set new file
		$newFile = (object)[
			'name' => $file['name'],
			'loc' => $path.'/'.$month.'/'.$file['name'],
			'type' => $file['type'],
			'size' => $file['size'],
		];
	}
	else
	{
		$newFile = null;
	}

	// check article data
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

	// update data
	$data = [];
	if ($_POST['article_srl']) $data[] = "article_srl='$_POST[article_srl]'";
	if (isset($_POST['ready'])) $data[] = "ready='$_POST[ready]'";
	if ($newFile)
	{
		$data[] = "name='$newFile->name'";
		$data[] = "loc='$newFile->loc'";
		$data[] = "type='$newFile->type'";
		$data[] = "size='$newFile->size'";
	}

	// set output
	$output = Controller::edit((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'file',
		'srl' => (int)$this->params['srl'],
		'data' => $data,
	]);

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