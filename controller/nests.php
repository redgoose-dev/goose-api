<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * Nests
 *
 * @var Goose $this
 */


$foo = new Model();

try {
	$aa = $foo->getCount();
	var_dump($aa);
} catch (Exception $e) {
	var_dump($e->getMessage());
}

//var_dump($this->target, $this->params, $foo, $aa);