<?php
namespace Core;
use Dotenv\Dotenv, Exception;

if (!defined('__GOOSE__')) exit();

// load autoload
require __DIR__.'/../vendor/autoload.php';

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

// set header
// check OPTIONS method
if (getenv('USE_CHECK_OPTIONS_METHOD') === 'true')
{
	if (
		$_SERVER['REQUEST_METHOD'] == 'OPTIONS' &&
		isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) &&
		(
			$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'POST' ||
			$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'GET'
		)
	)
	{
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Credentials: true");
		header('Access-Control-Allow-Headers: X-Requested-With,Content-Type,Authorization');
		header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
		header('Access-Control-Max-Age: 86400');
		exit;
	}
	else
	{
		header('Access-Control-Allow-Origin: *');
	}
}
else if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
{
	exit;
}

// set json header
header('Content-Type: application/json');

try
{
	// set development
	define('__DEBUG__', getenv('API_DEBUG') === 'true');

	// set error report
	if (__DEBUG__)
	{
		error_reporting(E_ALL & ~E_NOTICE);
	}
	else
	{
		error_reporting(0);
	}

	// set default timezone
	if (getenv('TIMEZONE'))
	{
		date_default_timezone_set(getenv('TIMEZONE'));
	}

	// set start time
	if (__DEBUG__)
	{
		define('__START_TIME__', microtime(true));
	}

	// set token
	define('__TOKEN__', $_SERVER['HTTP_AUTHORIZATION']);

	// check install
	Install::check();

	// get form data for json
	if (!$_POST && $formData = file_get_contents('php://input'))
	{
		$_POST = (array)json_decode($formData);
	}

	// set app
	$goose = new Goose();
	$goose->run();
}
catch(Exception $e)
{
	Error::data($e->getMessage());
}
