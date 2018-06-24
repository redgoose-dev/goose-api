<?php
if (!defined('__GOOSE__')) exit();


return [

	// intro
	[ 'GET', '/', 'intro', 'Intro' ],

	// nests
	[ 'GET', '/nests', 'nests', 'Nests' ],
	[ 'GET', '/nests/[:srl]', 'nest', 'Nest' ],

];