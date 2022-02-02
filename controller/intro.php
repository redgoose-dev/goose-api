<?php
namespace Core;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * hello word
 *
 * @var Goose|Connect $this
 */

try
{
  // set output
  $output = (object)[];
  $output->code = 200;
  $output->message = 'hello goose api';

  // output
  return Output::result($output);
}
catch(Exception $e)
{
  return Error::result($e->getMessage(), $e->getCode());
}
