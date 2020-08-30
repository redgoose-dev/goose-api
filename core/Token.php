<?php
namespace Core;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;


/**
 * Token
 */

class Token {

  const base_time = 60 * 60 * 24; // (s * m * h)

  /**
   * get time
   *
   * @param string
   * @return int
   */
  private static function getTime($type='access')
  {
    switch ($type)
    {
      case 'access':
        return self::base_time * (int)$_ENV['API_TOKEN_ACCESS_DAY'];

      case 'refresh':
        return self::base_time * (int)$_ENV['API_TOKEN_REFRESH_DAY'];
    }
    return 0;
  }

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
    $token->iss = $_ENV['API_PATH_URL'];
    $token->jti = $_ENV['API_TOKEN_ID'];
    if ($op->time) $token->iat = $now;
    if ($op->time && $op->exp) $token->exp = $now + self::getTime('access');
    $token->data = (isset($op->data)) ? $op->data : (object)[ 'type' => 'anonymous' ];

    // make encode
    $jwt = JWT::encode($token, $_ENV['API_TOKEN_KEY']);

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
    $key = $_ENV['API_TOKEN_KEY'];

    try
    {
      if (!$token) throw new Exception('No token');
      $decoded = JWT::decode($token, $key, ['HS256']);
      $output->url = $decoded->iss;
      if ($decoded->iat) $output->time = $decoded->iat;
      if ($decoded->exp) $output->exp = $decoded->exp;
      if ($decoded->jti) $output->token_id = $decoded->jti;
      $output->data = $decoded->data;
      return $output;
    }
    catch (ExpiredException $e)
    {
      JWT::$leeway = self::getTime('refresh');
      $decoded = JWT::decode($token, $key, ['HS256']);

      $now = time();
      $expire = $now + self::getTime('access');
      $decoded->iat = $now;
      $decoded->exp = $expire;

      $output->url = $decoded->iss;
      if ($decoded->iat) $output->time = $decoded->iat;
      if ($decoded->exp) $output->exp = $decoded->exp;
      if ($decoded->jti) $output->token_id = $decoded->jti;
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
