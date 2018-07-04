<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * edit article
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

	// get values
	$_PATCH = Util::getFormData();

	// check authorization
	$token = Auth::checkAuthorization($this->level->admin);

	// set output
	$output = Controller::edit((object)[
		'goose' => $this,
		'table' => 'article',
		'srl' => (int)$this->params['srl'],
		'data' => [
			$_PATCH['app_srl'] ? "app_srl='$_PATCH[app_srl]'" : '',
			$_PATCH['nest_srl'] ? "nest_srl='$_PATCH[nest_srl]'" : '',
			$_PATCH['category_srl'] ? "category_srl='$_PATCH[category_srl]'" : '',
			$_PATCH['user_srl'] ? "user_srl='$_PATCH[user_srl]'" : '',
			$_PATCH['title'] ? "title='$_PATCH[title]'" : '',
			$_PATCH['content'] ? "content='$_PATCH[content]'" : '',
			$_PATCH['hit'] ? "hit='$_PATCH[hit]'" : '',
			$_PATCH['json'] ? "json='$_PATCH[json]'" : '',
			"modate='".date("YmdHis")."'"
		],
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