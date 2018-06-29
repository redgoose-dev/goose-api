<?php
use Firebase\JWT\JWT, Firebase\JWT\ExpiredException;


$offset = 10;
$key = getenv('APP_KEY');
$now = time();
$expire = $now + $offset;

$token = [
	'iss' => getenv('PATH_URL'),
	'iat' => $now,
	'exp' => $expire,
	'data' => [
		'user_srl' => 1,
	]
];

//$decoded = JWT::decode($jwt, $key, ['HS256']);

if ($_GET['get'])
{
	$jwt = JWT::encode($token, $key);
	echo $jwt;
	exit;
}

try
{
	//$payload = JWT::decode($_GET['jwt'], $key, ['HS256']);
	$decoded = JWT::decode($_GET['jwt'], $key, ['HS256']);
	var_dump('=====================');
}
catch (ExpiredException $e)
{
	// 처음 만들어진 토큰이 만료되면 연장된 토큰을 새로 만든다.
	// 만약 새로 만들어진 토큰을 사용하지 않고 그대로 사용하다가 추가시간이 지나면 완전히 만료된다.
	//JWT::$leeway = 60 * 60 * 24; // 재발급할때의 연장시간
	JWT::$leeway = 20; // 재발급할때의 연장시간
	$decoded = (array)JWT::decode($_GET['jwt'], $key, ['HS256']);
	// TODO: test if token is blacklisted
	$now = time();
	$expire = $now + $offset;
	$decoded['iat'] = $now;
	$decoded['exp'] = $expire;

	echo "재발급 시도\n";
	echo JWT::encode($decoded, $key); // 연장된 토큰
}
catch ( \Exception $e )
{
	echo 'Exception message '.$e ;
}


print_r($decoded);