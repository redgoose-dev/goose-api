<?php
namespace Core;
use Dotenv\Dotenv, Exception;

if (!defined('__GOOSE__')) exit();


// load autoload
require __DIR__.'/../vendor/autoload.php';

try
{
	// set dotenv
	try
	{
		$dotenv = new Dotenv(__PATH__);
		$dotenv->load();
	}
	catch(Exception $e)
	{
		throw new Exception('.env error');
	}

	// set development
	define('__DEBUG__', getenv('API_DEBUG') === 'true');

	// set start time
	if (__DEBUG__)
	{
		define('__START_TIME__', microtime(true));
	}

	// check install
	Install::check();

	// set app
	$goose = new Goose();
	$goose->run();
}
catch(Exception $e)
{
	Error::data($e->getMessage());
}