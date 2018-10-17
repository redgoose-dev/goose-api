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
	// check post values
	Util::checkExistValue($_POST, [ 'nest_srl', 'srls' ]);

	// set model and connect db
	$model = new Model();
	$model->connect();

	// check access
	$token = Controller::checkAccessItem((object)[
		'model' => $model,
		'table' => 'nests',
		'srl' => (int)$_POST['nest_srl'],
	]);

	// set srls
	$srls = explode(',', $_POST['srls']);

	// update db
	foreach ($srls as $k=>$v)
	{
		$model->edit((object)[
			'table' => 'categories',
			'where' => 'srl='.$v,
			'data' => [ 'turn='.$k ],
			'debug' => true
		]);
	}

	// set output
	$output = (object)[];
	$output->code = 200;
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
