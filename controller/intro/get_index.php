<?php
namespace Core;

if (!defined('__GOOSE__')) exit();

/**
 * get intro
 *
 * @var Goose $this
 */

Output::data((object)[
	'message' => 'Welcome to goose api',
	'code' => 200,
]);
