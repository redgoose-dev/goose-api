<?php
if (!defined('__GOOSE__')) exit();


return [

	// intro
	[ 'GET', '/', 'intro/get_index', 'getIntro' ],

	// apps
	[ 'GET', '/apps', 'apps/get_index', 'getApps' ],

	// categories
	[ 'GET', '/categories', 'categories/get_index', 'getCategories' ],

	// nests
	[ 'GET', '/nests', 'nests/get_index', 'getNests' ],
	[ 'GET', '/nests/[:srl]', 'nests/get_item', 'getNest' ],

];