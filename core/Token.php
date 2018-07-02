<?php
namespace Core;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;


/**
 * Token
 */

class Token {

	const offset_access = 60 * 60 * 25 * 1; // access token 연장시간 (s * m * h * d)
	const offset_refresh = 60 * 60 * 24 * 15; // refresh token 연장시간 (s * m * h * d)

	/**
	 * make token
	 *
	 * @param object $op (time,exp,data)
	 * @return object
	 */
	public static function make($op=null)
	{
		if (!$op) return null;

		$now = time();

		// set token
		$token = (object)[];
		$token->iss = getenv('PATH_URL');
		$token->jti = getenv('TOKEN_ID');
		if ($op->time) $token->iat = $now;
		if ($op->time && $op->exp) $token->exp = $now + self::offset_access;
		$token->data = ($op->data) ? $op->data : (object)[ 'type' => 'anonymous' ];

		// make encode
		$jwt = JWT::encode($token, getenv('APP_KEY'));

		return (object)[
			'token' => $jwt,
			'option' => $token,
		];
	}

	/**
	 * get token
	 *
	 * @param string $token
	 * @return object
	 * @throws Exception
	 */
	public static function get($token)
	{
		$output = (object)[];
		$decoded = null;
		$key = getenv('APP_KEY');

		try
		{
			$decoded = JWT::decode($token, $key, ['HS256']);
			$output->url = $decoded->iss;
			if ($decoded->iat) $output->time = $decoded->iat;
			if ($decoded->exp) $output->exp = $decoded->exp;
			$output->data = $decoded->data;
			return $output;
		}
		catch (ExpiredException $e)
		{
			JWT::$leeway = self::offset_refresh;
			$decoded = JWT::decode($token, $key, ['HS256']);

			$now = time();
			$expire = $now + self::offset_access;
			$decoded->iat = $now;
			$decoded->exp = $expire;

			$output->url = $decoded->iss;
			if ($decoded->iat) $output->time = $decoded->iat;
			if ($decoded->exp) $output->exp = $decoded->exp;
			$output->data = $decoded->data;
			$output->token = JWT::encode($decoded, $key);
			return $output;
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage(), $e->getCode());
		}
	}

}