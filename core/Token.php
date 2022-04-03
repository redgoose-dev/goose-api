<?php
namespace Core;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

/**
 * Token
 */

class Token {

  const base_time = 60 * 60 * 24; // (s * m * h)
  const algo = 'HS256';

  /**
   * get time
   */
  private static function getTime(string $type = 'access'): int
  {
    switch ($type)
    {
      case 'access':
        return self::base_time * (int)$_ENV['API_TOKEN_ACCESS_DAY'];
      case 'refresh':
        return self::base_time * (int)$_ENV['API_TOKEN_REFRESH_DAY'];
      default:
        return 0;
    }
  }

  /**
   * make token
   *
   * @param object $op (time,exp,data)
   * @return object
   */
  public static function make(object $op): ?object
  {
    if (!$op) return null;
    $now = time();
    // set token
    $token = (object)[];
    $token->iss = $op->host;
    $token->jti = $_ENV['API_TOKEN_ID'];
    if ($op->time) $token->iat = $now;
    if ($op->time && $op->exp) $token->exp = $now + self::getTime('access');
    $token->data = (isset($op->data)) ? $op->data : (object)[];
    // make encode
    $jwt = JWT::encode((array)$token, $_ENV['API_TOKEN_KEY'], self::algo);
    return (object)[
      'token' => $jwt,
      'option' => $token,
    ];
  }

  /**
   * get token
   *
   * @throws Exception
   */
  public static function get(string $token): object
  {
    $output = (object)[];
    $decoded = null;
    $key = $_ENV['API_TOKEN_KEY'];

    try
    {
      if (!$token) throw new Exception('No token');
      $token = preg_replace('/^Bearer /', '', $token);
      $decoded = JWT::decode($token, new Key($key, self::algo));
      $output->url = $decoded->iss;
      if (isset($decoded->iat)) $output->time = $decoded->iat;
      if (isset($decoded->exp)) $output->exp = $decoded->exp;
      if (isset($decoded->jti)) $output->id = $decoded->jti;
      $output->data = $decoded->data;
      return $output;
    }
    catch (ExpiredException $e)
    {
      JWT::$leeway = self::getTime('refresh');
      // set decoded
      $decoded = JWT::decode($token, new Key($key, self::algo));
      $now = time();
      $expire = $now + self::getTime('access');
      $decoded->iat = $now;
      $decoded->exp = $expire;
      // set output
      $output->url = $decoded->iss;
      if (isset($decoded->iat)) $output->time = $decoded->iat;
      if (isset($decoded->exp)) $output->exp = $decoded->exp;
      if (isset($decoded->jti)) $output->id = $decoded->jti;
      $output->data = $decoded->data;
      $output->token = JWT::encode((array)$decoded, $key, self::algo);
      return $output;
    }
    catch (Exception $e)
    {
      throw new Exception($e->getMessage(), $e->getCode());
    }
  }

}
