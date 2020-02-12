<?php
namespace Core;
use Exception;

/**
 * File
 */

class File {

  /**
   * make directory
   *
   * @param string $path
   * @param int $permission
   */
  public static function makeDirectory($path, $permission=0707)
  {
    if (is_dir($path)) return;
    $umask = umask();
    umask(000);
    mkdir($path, $permission);
    umask($umask);
  }

  /**
   * check filename
   *
   * @param string $name
   * @param boolean $useRandomText
   * @return string
   */
  public static function checkFilename($name, $useRandomText=false)
  {
    if (!$name) return null;

    // set allow file type
    $allowFileType = getenv('FILE_ALLOW_TYPE');
    $allowFileType = explode(',', $allowFileType);

    // set source
    $src = [
      basename($name, strrchr($name, '.')),
      strtolower(substr(strrchr($name, '.'), 1))
    ];

    // check file type
    if (!in_array($src[1], $allowFileType)) return null;

    // only eng or number
    $src[0] = preg_replace("/[^A-Za-z0-9-_]+/", 'x', $src[0]);
    // remove special characters
    $src[0] = Text::removeSpecialChar($src[0]);

    // make random name
    if (!$src[0] || $useRandomText)
    {
      $src[0] = md5(date('YmdHis') . '-' . rand());
    }

    return $src[0] . '.' . $src[1];
  }

  /**
   * check exist file
   * 파일이름이 같은것이 있다면 이름뒤에 "-{x}"키워드를 붙인다.
   * 중복되는 이름이 있다면 x값을 올려서 붙인다.
   *
   * @param string $dir
   * @param string $file
   * @param number|null $n
   * @return string
   */
  public static function checkExistFile($dir='', $file='', $n=null)
  {
    if (!$file) return null;

    if (is_null($n))
    {
      $n = 0;
      $new = $file;
    }
    else
    {
      $n = $n + 1;
      $new = basename($file, strrchr($file, '.')) . '-' . $n . '.' . substr(strrchr($file, '.'), 1);
    }

    if (file_exists($dir . $new))
    {
      return self::checkExistFile($dir, $file, $n);
    }
    else
    {
      return $new;
    }
  }

  /**
   * get directories
   *
   * @param string $path
   * @return array
   */
  public static function getDirectories($path=null)
  {
    if (!$path) return [];
    $result = [];
    $dir_index = array_diff(scandir($path), ['.', '..', '.DS_Store']);
    foreach($dir_index as $item)
    {
      if (is_dir($path.'/'.$item)) $result[] = $item;
    }
    return $result;
  }

  /**
   * get files in directory
   *
   * @param string
   * @return array
   */
  public static function getFiles($path)
  {
    $result = [];
    $allowFileType = getenv('FILE_ALLOW_TYPE');
    $allowFileType = explode(',', $allowFileType);
    $items = array_diff(scandir($path), ['.', '..', '.DS_Store']);
    foreach ($items as $item)
    {
      $ext = strtolower(substr(strrchr($item, '.'), 1));
      if (!in_array($ext, $allowFileType)) continue;
      $result[] = $item;
    }
    return $result;
  }

}
