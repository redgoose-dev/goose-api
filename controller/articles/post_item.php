<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * add article
 *
 * @var Goose $this
 */

try
{
	// check post values
	Util::checkExistValue($_POST, [ 'app_srl', 'nest_srl', 'category_srl', 'user_srl', 'title', 'content' ]);

	// check authorization
	$token = Auth::checkAuthorization($this->level->admin);

	// filtering text
	$_POST['title'] = htmlspecialchars(addslashes($_POST['title']));
	$_POST['content'] = addslashes($_POST['content']);

	// set output
	$output = Controller::add((object)[
		'goose' => $this,
		'table' => 'article',
		'data' => (object)[
			'srl' => null,
			'app_srl' => $_POST['app_srl'],
			'nest_srl' => $_POST['nest_srl'],
			'category_srl' => $_POST['category_srl'],
			'user_srl' => $_POST['user_srl'],
			'title' => $_POST['title'],
			'content' => $_POST['content'],
			'hit' => 0,
			'json' => $_POST['json'],
			'ip' => ($_SERVER['REMOTE_ADDR'] !== '::1') ? $_SERVER['REMOTE_ADDR'] : 'localhost',
			'regdate' => date('YmdHis'),
			'modate' => date('YmdHis'),
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