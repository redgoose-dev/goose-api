<?php

return (object)[
	'path' => (object)[
		'url' => 'http://localhost:8000',
		'root' => '/goose'
	],
	'db' => (object)[
		'dbname' => 'goose',
		'name' => 'goose',
		'host' => '127.0.0.1',
//		'host' => 'localhost',
		'port' => 3306,
		'password' => '1212',
	],
	'table_prefix' => 'goose_',
	'basic_module' => 'Intro',
	'token' => '7f4e9b815255129820b5267f75beccf181a79c8297d47dcd3df2205f0cc1d616',
	'timezone' => 'Asia/Seoul',
	'accessLevel' => (object)[
		'login' => 100,
		'admin' => 100,
	],
];