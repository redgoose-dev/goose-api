<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * custom controller router
 *
 * @var Goose $this
 */

$path = __PATH__.'/controller/custom/';
$filename = strtolower($_SERVER['REQUEST_METHOD']).'_'.$this->params['name'].'.php';
var_dump($path.$filename);

// TODO: 파일을 검사하고 해당되는 파일로 require 하고 컨트롤을 넘기기