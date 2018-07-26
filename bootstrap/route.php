<?php
if (!defined('__GOOSE__')) exit();

return [

	// intro
	[ 'GET', '', 'intro' ],
	[ 'GET', '/', 'intro' ],
	[ 'GET', '/hello', 'hello' ],

	// apps
	[ 'GET', '/apps', 'apps/get_index', 'getApps' ],
	[ 'GET', '/apps/[i:srl]', 'apps/get_item', 'getApp' ],
	[ 'POST', '/apps', 'apps/add_item', 'addApp' ],
	[ 'POST', '/apps/[i:srl]/edit', 'apps/edit_item', 'editApp' ],
	[ 'POST', '/apps/[i:srl]/delete', 'apps/delete_item', 'deleteApp' ],

	// articles
	[ 'GET', '/articles', 'articles/get_index', 'getArticles' ],
	[ 'GET', '/articles/[i:srl]', 'articles/get_item', 'getArticle' ],
	[ 'POST', '/articles', 'articles/add_item', 'addArticle' ],
	[ 'POST', '/articles/[i:srl]/edit', 'articles/edit_item', 'editArticle' ],
	[ 'POST', '/articles/[i:srl]/delete', 'articles/delete_item', 'deleteArticle' ],

	// categories
	[ 'GET', '/categories', 'categories/get_index', 'getCategories' ],
	[ 'GET', '/categories/[i:srl]', 'categories/get_item', 'getCategory' ],
	[ 'POST', '/categories', 'categories/add_item', 'addCategory' ],
	[ 'POST', '/categories/[i:srl]/edit', 'categories/edit_item', 'editCategory' ],
	[ 'POST', '/categories/[i:srl]/delete', 'categories/delete_item', 'deleteCategory' ],
	[ 'POST', '/categories/sort', 'categories/sort_items', 'sortCategories' ],

	// files
	[ 'GET', '/files', 'files/get_index', 'getFiles' ],
	[ 'GET', '/files/[i:srl]', 'files/get_item', 'getFile' ],
	[ 'POST', '/files', 'files/add_item', 'addFile' ],
	[ 'POST', '/files/[i:srl]/edit', 'files/edit_item', 'editFile' ],
	[ 'POST', '/files/[i:srl]/delete', 'files/delete_item', 'deleteFile' ],
	[ 'POST', '/files/upload-file', 'files/upload_file', 'uploadFile' ],
	[ 'POST', '/files/remove-file', 'files/remove_file', 'removeFile' ],

	// json
	[ 'GET', '/json', 'json/get_index', 'getJsonIndex' ],
	[ 'GET', '/json/[i:srl]', 'json/get_item', 'getJson' ],
	[ 'POST', '/json', 'json/add_item', 'addJson' ],
	[ 'POST', '/json/[i:srl]/edit', 'json/edit_item', 'editJson' ],
	[ 'POST', '/json/[i:srl]/delete', 'json/delete_item', 'deleteJson' ],

	// nests
	[ 'GET', '/nests', 'nests/get_index', 'getNests' ],
	[ 'GET', '/nests/[i:srl]', 'nests/get_item', 'getNest' ],
	[ 'POST', '/nests', 'nests/add_item', 'addNest' ],
	[ 'POST', '/nests/[i:srl]/edit', 'nests/edit_item', 'editNest' ],
	[ 'POST', '/nests/[i:srl]/delete', 'nests/delete_item', 'deleteNest' ],

	// users
	[ 'GET', '/users', 'users/get_index', 'getUsers' ],
	[ 'GET', '/users/[i:srl]', 'users/get_item', 'getUser' ],
	[ 'POST', '/users', 'users/add_item', 'addUser' ],
	[ 'POST', '/users/[i:srl]/edit', 'users/edit_item', 'editUser' ],
	[ 'POST', '/users/[i:srl]/delete', 'users/delete_item', 'deleteUser' ],
	[ 'POST', '/users/[i:srl]/change-password', 'users/change_password', 'changePasswordUser' ],

	// auth
	[ 'POST', '/auth/login', 'auth/post_login', 'postLogin' ],
	[ 'POST', '/auth/logout', 'auth/post_logout', 'postLogout' ],

	// token
	[ 'GET', '/token/decode', 'token/get_decode', 'postTokenDecode' ],
	[ 'POST', '/token/clear', 'token/post_clear', 'postTokenClear' ],

	// maganer
	[ 'GET', '/manager/[aa:name]', 'manager/index', 'manager' ],
	[ 'GET', '/manager/[aa:name]/[aa:srl]', 'manager/index', 'managerParam' ],

	// external (user controller)
	[ 'GET', '/external/[aa:name]', 'external/index', 'getExternal' ],
	[ 'GET', '/external/[aa:name]/[aa:srl]', 'external/index', 'getExternalParam' ],
	[ 'POST', '/external/[aa:name]', 'external/index', 'postExternal' ],
	[ 'POST', '/external/[aa:name]/[aa:srl]', 'external/index', 'postExternalParam' ],
];