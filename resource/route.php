<?php
if (!defined('__GOOSE__')) exit();


return [

	// intro
	[ 'GET', '/', 'get/intro', 'Intro' ],

	// nests
	[ 'GET', '/nests', 'get/nests', 'Nests' ],
	[ 'GET', '/nests/[:srl]', 'get/nestsItem', 'Nest' ],

];