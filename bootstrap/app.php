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
	return;
}

// set development
define('__DEBUG__', getenv('API_DEBUG') === 'true');

// set app
if (Install::check())
{
	// check token
	if (!Goose::checkToken())
	{
		Error::data('Token error', 403);
		return;
	}

	$goose = new Goose();
	$goose->run();
}
else
{
	Output::json((object)[
		'message' => 'Installation is required.',
		'code' => 500
	]);
}
