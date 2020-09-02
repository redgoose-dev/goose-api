<?php
namespace Core;
use Dotenv\Dotenv, Exception;

if (!defined('__API_GOOSE__')) exit();

// load autoload
require __DIR__.'/../vendor/autoload.php';

// set dotenv
try
{
  $dotenv = Dotenv::createImmutable(__API_PATH__);
  $dotenv->load();
}
catch(Exception $e)
{
  throw new Exception('.env error');
}

// set header
// check OPTIONS method
if ($_ENV['API_USE_CHECK_OPTIONS_METHOD'] === 'true')
{
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Headers: origin, content-type, accept, Authorization');
  header('Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS, DELETE');
  header('Access-Control-Max-Age: 3600');

  if (
    $_SERVER['REQUEST_METHOD'] == 'OPTIONS' &&
    isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) &&
    (
      $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'POST' ||
      $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'GET'
    )
  )
  {
    exit;
  }
}
else if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
{
  exit;
}

// set json header
header('Content-Type: application/json,text/plane;charset=UTF-8');

// set mode
define('__API_MODE__', 'api');

// set development
define('__API_DEBUG__', $_ENV['API_DEBUG'] === 'true');

// set token
define('__API_TOKEN__', isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : null);

if (__API_DEBUG__)
{
  // set start time
  define('__API_START_TIME__', microtime(true));
  // set error report
  error_reporting(E_ALL & ~E_NOTICE);
}
else
{
  // set error report
  error_reporting(0);
}

// set default timezone
if ($_ENV['API_TIMEZONE'])
{
  date_default_timezone_set($_ENV['API_TIMEZONE']);
}

try
{
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
