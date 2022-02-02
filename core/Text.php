<?php
namespace Core;
use Exception;

/**
 * Text
 */

class Text {

  /**
   * remove special characters
   * 특수문자를 제거한다.("-"와 "_"는 제외) 공백은 "_"로 변환한다.
   */
  public static function removeSpecialChar(string $str): string
  {
    if (!$str) return '';
    $str = preg_replace("/\s+/", "_", $str);
    return preg_replace ("/[#\&\+%@=\/\\\:;,\.'\"\^`~|\!\?\*$#<>()\[\]\{\}]/i", "", $str);
  }

  /**
   * allow string
   */
  public static function allowString(string $value, ?string $type): bool
  {
    $value = trim($value);
    return match ($type) {
      'string' => !!preg_match("/^[a-zA-Z]+$/", $value),
      'number' => !!preg_match("/^[0-9]+$/", $value),
      default => !!preg_match("/^[a-zA-Z0-9_-]+$/", $value),
    };
  }

  /**
   * create password
   */
  public static function createPassword(string $str): string
  {
    return password_hash($str, PASSWORD_DEFAULT);
  }

  /**
   * printf
   * `I'am a {0}` 같은 형식을 두번째 파라메터부터 값을 넣어주는 도구
   */
  public static function printf(string $str, ...$args): string
  {
    foreach ($args as $key=>$value)
    {
      $str = preg_replace("/\{{$key}\}/", $value, $str);
    }
    return $str;
  }

  /**
   * check email
   * @throws Exception
   */
  public static function checkEmail(string $email): void
  {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    {
      throw new Exception(Message::make('error.email'));
    }
  }

}
