<?php
namespace Core;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * external controller router
 *
 * @var Goose $this
 */

// set variables
$path = __API_PATH__.'/controller/external/';
$filename = strtolower($_SERVER['REQUEST_METHOD']).'_'.$this->params['name'].'.php';

try
{
  // check file
  if (!file_exists($path.$filename))
  {
    throw new Exception('Not found controller.', 404);
  }
}
catch(Exception $e)
{
  Error::result($e->getMessage(), $e->getCode());
}


// require file
require_once $path.$filename;