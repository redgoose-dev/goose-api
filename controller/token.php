<?php
use Firebase\JWT\JWT;


$key = getenv('APP_KEY');
$issuedAt = time();
$notBefore = $issuedAt;
$expire = $notBefore + 60;

$token = [
	'iss' => getenv('PATH_URL'),
	'iat' => $issuedAt,
	'nbf' => $notBefore,
	'exp' => $expire,
	'data' => [
		'user_srl' => 1,
		'ip' => null, // TODO: IP정보
	]
];

$jwt = JWT::encode($token, $key);
//$decoded = JWT::decode($jwt, $key, array('HS256'));
try {
	$decoded = JWT::decode($_GET['jwt'], $key, array('HS256'));
} catch (Exception $e) {
	$decoded = null;
}


var_dump($jwt, $decoded);
