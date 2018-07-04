<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * sort turn category
 *
 * @form-data string srls `4,5,6,7`
 *
 * @var Goose $this
 */

try
{
	// get values
	$_PATCH = Util::getFormData();
	//var_dump($_PATCH);

	// check post values
	Util::checkExistValue($_PATCH, [ 'srls' ]);

	// check authorization
	$token = Auth::checkAuthorization($this->level->admin);

	// set srls
	$srls = explode(',', $_PATCH['srls']);

	// set model and connect db
	$model = new Model();
	$model->connect();

	// update db
	foreach ($srls as $k=>$v)
	{
		$model->edit((object)[
			'table' => 'category',
			'where' => 'srl='.$v,
			'data' => [ 'turn='.$k ],
			'debug' => true
		]);
	}

	// disconnect db
	$model->disconnect();

	// set output
	$output = (object)[];
	$output->code = 200;
	if ($token) $output->_token = $token;

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
