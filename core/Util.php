<?php
namespace Core;
use Exception;

/**
 * Util
 */

class Util {

  /**
   * get url parameter
   *
   * @param string $str
   * @return string
   */
  public static function getParameter($str='')
  {
    if (isset($_POST[$str]))
    {
      return $_POST[$str];
    }
    else if (isset($_GET[$str]))
    {
      return $_GET[$str];
    }
    else
    {
      return null;
    }
  }

  /**
   * check exist value
   * 배열속에 필수값이 들어있는지 확인
   *
   * @param array|object|null $target 확인할 배열
   * @param array|null $required 키값이 들어있는 배열
   * @throws Exception
   */
  public static function checkExistValue($target=null, $required=null)
  {
    if (!isset($target)) throw new Exception('No value `$target`');
    if ($required)
    {
      $target = (array)$target;
      foreach ($required as $k=>$v)
      {
        if (!array_key_exists($v, $target) || !$target[$v])
        {
          throw new Exception('Can not find `'.$v.'`.', 204);
        }
      }
    }
  }

  /**
   * create directory
   *
   * @param string|null $path
   * @param int $permission
   * @throws Exception
   */
  public static function createDirectory($path=null, $permission=0707)
  {
    if (is_dir($path))
    {
      throw new Exception('Directory already exists.');
    }
    else
    {
      $umask = umask();
      umask(000);
      mkdir($path, $permission);
      umask($umask);
    }
  }

  /**
   * check key in the extra field
   *
   * @param string|null $keyword
   * @param string|null $field
   * @return boolean
   */
  public static function checkKeyInExtField($keyword=null, $field=null)
  {
    if (!($field = $field ? $field : $_GET['ext_field']) || !$keyword) return false;
    $arr = explode(',', $field);
    return (array_search($keyword, $arr) === false) ? false : true;
  }

  /**
   * convert fields
   *
   * @param string|null $fields
   * @return string
   */
  public static function convertFields($fields=null)
  {
    if (!$fields || $fields === '*') return '*';

    $arr = explode(',', $fields);
    for ($i=0; $i<count($arr); $i++)
    {
      $arr[$i] = "`".$arr[$i]."`";
    }
    $result = implode(',', $arr);
    $result = preg_replace("/\`\`/i", "`", $result);
    return $result;
  }

  /**
   * get controller path
   *
   * @param string $target
   * @return string|null
   * @throws Exception
   */
  public static function getControllerPath($target='')
  {
    // check $target
    if (!$target)
    {
      throw new Exception(Message::make('error.notFound', 'target'), 404);
    }
    // search controller
    if (!file_exists(__API_PATH__.'/controller/'.$target.'.php'))
    {
      throw new Exception(Message::make('error.notFound', 'controller'), 404);
    }
    return __API_PATH__.'/controller/'.$target.'.php';
  }
}
