<?php
/**
 * Goose API Connect
 *
 * @package goose-api
 * @author redgoose <https://redgoose.me>
 */

// set keyword `__API_GOOSE__`
define('__API_GOOSE__', true);

// set path for global
define('__API_PATH__', __DIR__);

// bootstrap
return require_once 'bootstrap/libs.php';
