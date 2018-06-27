<?php
namespace Core;

if (!defined('__GOOSE__')) exit();

/**
 * get intro
 *
 * @var Goose $this
 */

Output::json((object)[
	'message' => 'Welcome to goose api',
	'code' => 200,
]);
