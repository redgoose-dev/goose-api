<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * upload file
 *
 * @_FILES array file
 * @_POST string sub_dir
 * @_POST string file
 *
 * @var Goose $this
 */

try
{
	// set values
	$subDir = ($_POST['sub_dir']) ? $_POST['sub_dir'] : 'user';
	$month = date('Ym');

	// set path
	$path = 'data/upload/'.$subDir;
	$path_absolute = __PATH__.'/'.$path;
	$path_absolute_dest = $path_absolute.'/'.$month;

	// multipart upload
	if ($_FILES['file'])
	{
		$file = $_FILES['file'];

		// check file error
		if ($file['error']) throw new Exception('Error file');

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

		// check exist file
		$file['name'] = File::checkExistFile($path_absolute_dest.'/', $file['name'], null);

		// make month directory
		File::makeDirectory($path_absolute, 0755);

		// make month directory
		File::makeDirectory($path_absolute_dest, 0755);

		if (!($file['tmp_name'] && is_dir($path_absolute_dest)))
		{
			throw new Exception('upload error');
		}

		// copy file
		move_uploaded_file($file['tmp_name'], $path_absolute_dest.'/'.$file['name']);

		// set output
		$data = (object)[
			'name' => $file['name'],
			'path' => $path.'/'.$month.'/'.$file['name'],
			'type' => $file['type'],
			'size' => $file['size'],
		];
	}
	// base64 upload
	else if ($_POST['file'])
	{
		// make image
		$imgData = $_POST['file'];
		if (preg_match("/^data:image\/jpeg/", $imgData))
		{
			$filename = uniqid().'.jpg';
			$imgData = str_replace('data:image/jpeg;base64,', '', $imgData);
			$imgType = 'image/jpeg';
		}
		else if (preg_match("/^data:image\/png/", $imgData))
		{
			$filename = uniqid().'.png';
			$imgData = str_replace('data:image/png;base64,', '', $imgData);
			$imgType = 'image/png';
		}
		else
		{
			throw new Exception('Only jpg and png files can be uploaded.');
		}
		$imgData = str_replace(' ', '+', $imgData);
		$imgSource = base64_decode($imgData);

		// check file size
		if ((int)strlen($imgSource) > (int)getenv('FILE_LIMIT_SIZE'))
		{
			throw new Exception('The attachment size exceeds the allowable limit.');
		}

		// check filename
		$filename = File::checkFilename($filename, false);
		if (!$filename)
		{
			throw new Exception('This file is a format that is not allowed.');
		}

		// check exist file
		$filename = File::checkExistFile($path_absolute_dest.'/', $filename, null);

		// make month directory
		File::makeDirectory($path_absolute, 0755);

		// make month directory
		File::makeDirectory($path_absolute_dest, 0755);

		// check dest dir
		if (!is_dir($path_absolute_dest))
		{
			throw new Exception('upload error');
		}

		// upload file
		$uploadedFileSize = file_put_contents($path_absolute_dest.'/'.$filename, $imgSource);

		// set data
		$data = (object)[
			'name' => $filename,
			'path' => $path.'/'.$month.'/'.$filename,
			'type' => $imgType,
			'size' => $uploadedFileSize,
		];
	}
	else
	{
		throw new Exception('No value to upload.');
	}

	// set output
	$output = (object)[];
	$output->code = 200;
	$output->data = $data;

	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}