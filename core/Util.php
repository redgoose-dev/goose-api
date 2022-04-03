<?php
namespace Core;
use Exception;

/**
 * Util
 * 유틸리티 메서드 모음
 */
class Util {

  /**
   * check exist value
   * 배열속에 필수값이 들어있는지 확인
   *
   * @param array|object $target 확인할 배열
   * @param array $required 키값이 들어있는 배열
   * @throws Exception
   */
  public static function checkExistValue(array|object $target, array $required = []): void
  {
    if (!$target) throw new Exception('No value `$target`');
    if ($required && count($required) > 0)
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
   * @throws Exception
   */
  public static function createDirectory(?string $path=null, int $permission = 0707): void
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
   */
  public static function checkKeyInExtField(string $keyword, ?string $field): bool
  {
    if (!($field && $keyword)) return false;
    $arr = explode(',', $field);
    return in_array($keyword, $arr);
  }

  /**
   * convert fields
   */
  public static function convertFields(string $fields): string
  {
    if (!$fields || $fields === '*') return '*';
    $arr = explode(',', $fields);
    for ($i = 0; $i < count($arr); $i++)
    {
      $arr[$i] = "`".$arr[$i]."`";
    }
    $result = implode(',', $arr);
    return preg_replace("/\`\`/i", "`", $result);
  }

  /**
   * controller router
   *
   * @param string $target
   * @return string
   * @throws Exception
   */
  public static function controllerRouter(string $target = ''): string
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

  /**
   * string to json
   */
  public static function stringToJson(?string $str): mixed
  {
    return json_decode(urldecode($str ?? ''), false);
  }

  /**
   * json to string
   */
  public static function jsonToString(mixed $json): string
  {
    return urlencode(json_encode($json, false));
  }

  /**
   * test json data
   *
   * @throws Exception
   */
  public static function testJsonData(string $json): string
  {
    try
    {
      $json = self::stringToJson($json);
      if (!$json) throw new Exception('error');
      return self::jsonToString($json);
    }
    catch(Exception $e)
    {
      throw new Exception(Message::make('error.json'));
    }
  }

  /**
   * Check directories
   * @throws Exception
   */
  public static function checkDirectories()
  {
    // check `/data`
    if (!is_dir(__API_PATH__.'/data'))
    {
      throw new Exception('The directory `/data` does not exist.');
    }
    if (!is_writable(__API_PATH__.'/data'))
    {
      throw new Exception('The `/data` directory permission is invalid.');
    }
    // check `/data/upload`
    if (!is_dir(__API_PATH__.'/data/upload'))
    {
      throw new Exception('The directory `/data/upload` does not exist.');
    }
    if (!is_writable(__API_PATH__.'/data/upload'))
    {
      throw new Exception('The `/data/upload` directory permission is invalid.');
    }
  }

  /**
   * get host
   * @param string $url
   * @return string
   */
  public static function getHost(string $url): string
  {
    return preg_replace("/^http(s?):\/\//", '', $url);
  }

}
