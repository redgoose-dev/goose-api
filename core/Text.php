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
   * @param array $str
   * @return string
   */
  public static function removeSpecialChar($str=null)
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
   * @param string $type
   * @return boolean
   */
  public static function allowString($value, $type=null)
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

}
