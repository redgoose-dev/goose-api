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
	$ready = isset($_POST['article_srls']) ? 0 : 1;

	// string to array files
	if (!is_array($_FILES['files']['name']))
	{
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

	// set path
	$path = 'data/upload/'.(($_POST['sub_dir']) ? $_POST['sub_dir'] : 'original');
	$path_absolute = __PATH__.'/'.$path;
	var_dump($path, $path_absolute);
	exit;

	// play upload
	foreach ($file['name'] as $k=>$v)
	{
		var_dump($file['name'][$k]);
	}

}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
