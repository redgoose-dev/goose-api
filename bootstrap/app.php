<?php
namespace Core;
use Dotenv\Dotenv, Exception;
if (!defined('__GOOSE__')) exit();


// load autoload
$autoload = require __DIR__.'/../vendor/autoload.php';

// set dotenv
try
{
	$dotenv = new Dotenv(__PATH__);
	$dotenv->load();
}
catch(Exception $e)
{
	Output::json((object)[
		'message' => 'ENV ERROR',
		'code' => 500
	]);
}

// set development
define('__DEBUG__', getenv('APP_DEBUG') === 'true');

// set install
$install = new Install();

// set app
if ($install->check())
{
	$goose = new Goose(__PATH__);
	$goose->run();
}
else
{
	$install->form();
	return null;
}
