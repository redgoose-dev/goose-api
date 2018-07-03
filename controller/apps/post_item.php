<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * add app
 *
 * @var Goose $this
 */

try
{
	// check authorization
	$token = Auth::checkAuthorization($this->level->admin);

	// check post values
	Util::checkExistValue($_POST, [ 'id', 'name' ]);

	// id check
	if (!Util::allowString($_POST['id']))
	{
		throw new Exception('`id` can be used only in numbers and English.');
	}

	// set output
	$output = Controller::add((object)[
		'goose' => $this,
		'table' => 'app',
		'data' => (object)[
			'srl' => null,
			'id' => $_POST['id'],
			'name' => $_POST['name'],
			'regdate' => date('YmdHis'),
		]
	]);

	// set token
	if ($token) $output->_token = $token;

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}