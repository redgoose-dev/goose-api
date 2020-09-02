<?php
namespace Core;

/**
 * Text
 */

class Text {

  /**
   * remove special characters
   * 특수문자를 제거한다.("-"와 "_"는 제외) 공백은 "_"로 변환한다.
   *
   * @param string $str
   * @return string
   */
  public static function removeSpecialChar(string $str='')
  {
    if (!$str) return '';
    $str = preg_replace("/\s+/", "_", $str);
    $str = preg_replace ("/[#\&\+%@=\/\\\:;,\.'\"\^`~|\!\?\*$#<>()\[\]\{\}]/i", "", $str);
    return $str;
  }

  /**
   * allow string
   *
   * @param string $value
   * @param string|null $type
   * @return boolean
   */
  public static function allowString(string $value, string $type='')
  {
    $value = trim($value);
    switch ($type)
    {
      case 'string':
        return !!preg_match("/^[a-zA-Z]+$/", $value);
      case 'number':
        return !!preg_match("/^[0-9]+$/", $value);
      default:
        return !!preg_match("/^[a-zA-Z0-9_-]+$/", $value);
    }
  }

  /**
   * create password
   *
   * @param string $str
   * @return string
   */
  public static function createPassword(string $str='')
  {
    return password_hash($str, PASSWORD_DEFAULT);
  }

  /**
   * printf
   * `I'am a {0}` 같은 형식을 두번째 파라메터부터 값을 넣어주는 도구
   *
   * @param string $str
   * @param array $args
   * @return string
   */
  public static function printf(string $str='', ...$args)
  {
    foreach ($args as $key=>$value)
    {
      $str = preg_replace("/\{{$key}\}/", $value, $str);
    }
    return $str;
  }

}
