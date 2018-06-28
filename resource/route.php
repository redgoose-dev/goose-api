<?php
if (!defined('__GOOSE__')) exit();


return [

	// intro
	[ 'GET', '/', 'intro/get_index', 'getIntro' ],

	// apps
	[ 'GET', '/apps', 'apps/get_index', 'getApps' ],

	// articles
	[ 'GET', '/articles', 'articles/get_index', 'getArticles' ],

	// categories
	[ 'GET', '/categories', 'categories/get_index', 'getCategories' ],

	// files
	[ 'GET', '/files', 'files/get_index', 'getFiles' ],

	// json
	[ 'GET', '/json', 'json/get_index', 'getJson' ],

	// nests
	[ 'GET', '/nests', 'nests/get_index', 'getNests' ],
	[ 'GET', '/nests/[:srl]', 'nests/get_item', 'getNest' ],

	// users
	[ 'GET', '/users', 'users/get_index', 'getUsers' ],

];