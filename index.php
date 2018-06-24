<?php
/**
 * Goose API
 *
 * @package goose-api
 * @author redgoose <https://redgoose.me>
 */


// set keyword `__GOOSE__`
define('__GOOSE__', true);

// set development
define('__DEBUG__', true);

// set path for global
define('__PATH__', __DIR__);

// bootstrap
require 'bootstrap/app.php';
