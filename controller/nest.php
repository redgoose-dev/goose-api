<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * Nest
 *
 * @var Goose $this
 */

// check token
if (getallheaders()['token'] !== $this->config->token)
{
	return Error::data('Token error', 403);
}

try
{
	// get instance
	$model = new Model();

	// connect db
	$tmp = $model->connect($this->config->db, $this->config->table_prefix);
	if ($tmp)
	{
		throw new Exception($tmp->getMessage(), $tmp->getCode());
	}

	if (!((int)$this->params['srl'] && $this->params['srl'] > 0))
	{
		throw new Exception('Not found srl', 404);
	}

	// get data
	$item = $model->getItem((object)[
		table => 'nest',
		field => $_GET['field'],
		json_field => ['json'],
		where => 'srl='.(int)$this->params['srl'],
		debug => false,
	]);

	// disconnect db
	$model->disconnect();

	// output data
	Output::json((object)[
		code => 200,
		data => $item,
	], $_GET['min']);
}
catch (Exception $e)
{
	Output::json((object)[
		code => $e->getCode(),
		message => $e->getMessage()
	]);
}
