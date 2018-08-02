<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * delete file
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

	/**
	 * remove file
	 */
	// get item
	$file = $model->getItem((object)[
		'table' => 'files',
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
		unlink(__PATH__.'/'.$file->data->loc);
	}

	// remove item
	$output = Controller::delete((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'files',
		'srl' => (int)$this->params['srl'],
	]);

	// set output
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
