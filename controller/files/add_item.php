<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * add file
 *
 * @var Goose $this
 */

try
{
	// check file
	if (!($_FILES['files'] && $_FILES['files']['name']))
	{
		throw new Exception('No files found.');
	}

	// set model
	$model = new Model();
	$model->connect();

	// check authorization
	$token = Auth::checkAuthorization($this->level->admin, $model);

	// set ready
	if (isset($_POST['article_srls']))
	{
		$ready = 0;
		$article_srls = explode(',', $_POST['article_srls']);
	}
	else
	{
		$ready = 1;
	}

	// string to array files
	if (!is_array($_FILES['files']['name']))
	{
		$file = [];
		$file['name'] = [ $_FILES['files']['name'] ];
		$file['type'] = [ $_FILES['files']['type'] ];
		$file['tmp_name'] = [ $_FILES['files']['tmp_name'] ];
		$file['size'] = [ $_FILES['files']['size'] ];
		$file['error'] = [ $_FILES['files']['error'] ];
	}
	else
	{
		$file = $_FILES['files'];
	}

	// set variable
	$result = [];
	$month = date('Ym');
	$subDir = ($_POST['sub_dir']) ? $_POST['sub_dir'] : getenv('DEFAULT_UPLOAD_DIR_NAME');

	// set path
	$path = 'data/upload/'.$subDir;
	$path_absolute = __PATH__.'/'.$path;
	$path_absolute_dest = $path_absolute.'/'.$month;

	// make sub directory
	File::makeDirectory($path_absolute, 0707);

	// make month directory
	File::makeDirectory($path_absolute_dest, 0707);

	// play upload
	foreach ($file['name'] as $k=>$v)
	{
		if ($file['error'][$k])
		{
			$result[] = (object)[
				'status' => 'error',
				'message' => $file['error'][$k]
			];
			continue;
		}

		// check file size
		if ((int)$file['size'][$k] > (int)getenv('FILE_LIMIT_SIZE'))
		{
			$result[] = (object)[
				'status' => 'error',
				'message' => 'The attachment size exceeds the allowable limit.'
			];
			continue;
		}

		// check filename
		$file['name'][$k] = File::checkFilename($file['name'][$k], false);
		if (!$file['name'][$k])
		{
			$result[] = (object)[
				'status' => 'error',
				'message' => 'This file is a format that is not allowed.'
			];
			continue;
		}

		// check exist file
		$file['name'][$k] = File::checkExistFile($path_absolute_dest.'/', $file['name'][$k], null);

		// copy file to target
		if ($file['tmp_name'][$k] && is_dir($path_absolute_dest))
		{
			move_uploaded_file($file['tmp_name'][$k], $path_absolute_dest.'/'.$file['name'][$k]);
		}
		else
		{
			$result[] = (object)[
				'status' => 'error',
				'message' => 'upload error'
			];
			continue;
		}

		// insert data
		try
		{
			$model->add((object)[
				'goose' => $this,
				'table' => 'files',
				'data' => (object)[
					'srl' => null,
					'article_srl' => isset($article_srls) ? (int)$article_srls[$k] : null,
					'name' => $file['name'][$k],
					'loc' => $path.'/'.$month.'/'.$file['name'][$k],
					'type' => $file['type'][$k],
					'size' => (int)$file['size'][$k],
					'regdate' => date("YmdHis"),
					'ready' => $ready,
				],
				'debug' => __DEBUG__,
			]);

			// set result
			$result[] = (object)[
				'status' => 'success',
				'loc' => $path.'/'.$month.'/'.$file['name'][$k],
				'name' => $file['name'][$k],
				'size' => $file['size'][$k],
				'type' => $file['type'][$k],
				'srl' => $model->getLastIndex(),
				'ready' => $ready,
			];
		}
		catch (Exception $e)
		{
			// remove file
			if (file_exists($path_absolute_dest.'/'.$file['name'][$k]))
			{
				@unlink($path_absolute_dest.'/'.$file['name'][$k]);
			}
			$result[] = (object)[ 'status' => 'error', 'message' => 'DB ERROR: '.$e->getMessage() ];
			continue;
		}
	}

	// set token
	$output = (object)[];
	$output->code = 200;
	if ($token) $output->_token = $token;
	$output->data = $result;

	// output data
	Output::data($output);

}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}