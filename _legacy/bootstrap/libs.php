<?php
namespace Core;

// load autoload
require __DIR__.'/../vendor/autoload.php';

// setup dotenv
try {
  Util::setupEnv(__API_PATH__);
} catch (\Exception $e) {
  return null;
}

// set mode
define('__API_MODE__', 'library');

// set development
define('__API_DEBUG__', $_ENV['API_DEBUG'] === 'true');

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

return new Connect();
