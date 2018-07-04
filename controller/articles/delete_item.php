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
	// check srl
	if (!((int)$this->params['srl'] && $this->params['srl'] > 0))
	{
		throw new Exception('Not found srl', 500);
	}

	// check authorization
	$token = Auth::checkAuthorization($this->level->admin);

	// remove item
	$output = Controller::delete((object)[
		'goose' => $this,
		'table' => 'article',
		'srl' => (int)$this->params['srl'],
	]);

	// set output
	if ($token) $output->_token = $token;

	// output data
	Output::data($output);

}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}