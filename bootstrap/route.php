<?php
if (!defined('__GOOSE__')) exit();


return [

	// intro
	[ 'GET', '/', 'intro/get_index', 'getIntro' ],

	// manager
	[ 'GET', '/manager', 'manager/get_page' ],
	[ 'GET', '/manager/', 'manager/get_page' ],
	[ 'GET', '/manager/[*]', 'manager/get_page' ],

	// apps
	[ 'GET', '/apps', 'apps/get_index', 'getApps' ],
	[ 'GET', '/apps/[i:srl]', 'apps/get_item', 'getApp' ],
	[ 'POST', '/apps', 'apps/post_item', 'postApp' ],
	[ 'PATCH', '/apps/[i:srl]', 'apps/patch_item', 'patchApp' ],
	[ 'DELETE', '/apps/[i:srl]', 'apps/delete_item', 'deleteApp' ],

	// articles
	[ 'GET', '/articles', 'articles/get_index', 'getArticles' ],
	[ 'GET', '/articles/[i:srl]', 'articles/get_item', 'getArticle' ],
	[ 'POST', '/articles', 'articles/post_item', 'postArticle' ],
	[ 'PATCH', '/articles/[i:srl]', 'articles/patch_item', 'patchArticle' ],
	[ 'DELETE', '/articles/[i:srl]', 'articles/delete_item', 'deleteArticle' ],

	// categories
	[ 'GET', '/categories', 'categories/get_index', 'getCategories' ],
	[ 'GET', '/categories/[i:srl]', 'categories/get_item', 'getCategory' ],
	[ 'POST', '/categories', 'categories/post_item', 'postCategory' ],
	[ 'PATCH', '/categories/[i:srl]', 'categories/patch_item', 'patchCategory' ],
	[ 'PATCH', '/categories/sort', 'categories/patch_sort', 'patchCategorySort' ],
	[ 'DELETE', '/categories/[i:srl]', 'categories/delete_item', 'deleteCategory' ],

	// files
	[ 'GET', '/files', 'files/get_index', 'getFiles' ],

	// json
	[ 'GET', '/json', 'json/get_index', 'getJson' ],

	// nests
	[ 'GET', '/nests', 'nests/get_index', 'getNests' ],
	[ 'GET', '/nests/[i:srl]', 'nests/get_item', 'getNest' ],

	// users
	[ 'GET', '/users', 'users/get_index', 'getUsers' ],
	[ 'POST', '/users', 'users/post_item', 'postUser' ],

	// auth
	[ 'POST', '/auth/login', 'auth/post_login', 'postLogin' ],
	[ 'POST', '/auth/logout', 'auth/post_logout', 'postLogout' ],
	[ 'POST', '/auth/token-make', 'auth/post_token-make', 'postTokenMake' ], // TODO: 삭제예정
	[ 'POST', '/auth/token-decode', 'auth/post_token-decode', 'postTokenDecode' ], // TODO: 삭제예정
	[ 'POST', '/auth/token-revoke', 'auth/post_token-revoke', 'postTokenRevoke' ],

];