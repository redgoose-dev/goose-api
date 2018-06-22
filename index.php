<?php
/**
 * Goose API
 *
 * @package goose-api
 * @author redgoose <https://redgoose.me>
 */


// Set keyword `__GOOSE__`
define('__GOOSE__', true);


// bootstrap
$app = require 'bootstrap/app.php';


// run application
if ($app)
{
	$app->run(__DIR__);
}
