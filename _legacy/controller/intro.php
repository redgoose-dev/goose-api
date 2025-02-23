<?php
namespace Core;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * intro
 *
 * @var Goose|Connect $this
 */

try
{
  // get composer.json
  $fileComposer = __API_PATH__.'/composer.json';
  $file = fopen($fileComposer, 'r');
  $composer = json_decode(fread($file, filesize($fileComposer)));
  fclose($file);

  // set output
  $output = (object)[];
  $output->code = 200;
  $output->message = 'hello goose api';
  $output->version = $composer->version;

  // output
  return Output::result($output);
}
catch(Exception $e)
{
  return Error::result($e->getMessage(), $e->getCode());
}
