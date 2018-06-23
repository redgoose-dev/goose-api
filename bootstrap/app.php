<?php
if (!defined('__GOOSE__')) exit();


// load autoload
$autoload = require __DIR__.'/../vendor/autoload.php';


// set install
$install = new \Core\Install();


// set app
if ($install->check())
{
	$goose = new \Core\Goose();
	$goose->run(__DIR__.'/../');
}
else
{
	$install->form();
	return null;
}
