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
  public static function makeDirectory(string $path, $permission=0707)
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
  public static function checkFilename(string $name, $useRandomText=false)
  {
    if (!$name) return null;

    // set allow file type
    $allowFileType = $_ENV['API_FILE_ALLOW_TYPE'];
    $allowFileType = explode(',', $allowFileType);

    // set source
    $src = [
      basename($name, strrchr($name, '.')),
      strtolower(substr(strrchr($name, '.'), 1))
    ];

    // check file type
    if (!in_array($src[1], $allowFileType)) return null;

    // only eng or number
    $src[0] = preg_replace("/[^A-Za-z0-9-_]+/", '-', $src[0]);
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
  public static function checkExistFile(string $dir, string $file, $n=null)
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
   * @param string|null $path
   * @return array
   */
  public static function getDirectories(string $path=null)
  {
    try
    {
      if (!$path) throw new Exception('not path');
      if (!is_dir($path)) throw new Exception('No such directory.');
      $dir_index = array_diff(scandir($path), ['.', '..', '.DS_Store']);
      $result = [];
      foreach($dir_index as $item)
      {
        if (is_dir($path.'/'.$item)) $result[] = $item;
      }
      return $result;
    }
    catch(Exception $e)
    {
      return [];
    }
  }

  /**
   * get files in directory
   *
   * @param string
   * @return array
   */
  public static function getFiles(string $path)
  {
    $result = [];
    $allowFileType = $_ENV['API_FILE_ALLOW_TYPE'];
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

  /**
   * convert files value
   * `$_FILES`에 들어있는 값을 multiple 형태로 변환시켜서 구조를 통일시킨다.
   *
   * @param array|null $files
   * @return array
   */
  public static function convertFilesValue(array $files=null)
  {
    if (isset($files['name']) && !is_array($files['name']))
    {
      return [
        'name' => [ $files['name'] ],
        'type' => [ $files['type'] ],
        'tmp_name' => [ $files['tmp_name'] ],
        'size' => [ $files['size'] ],
        'error' => [ $files['error'] ],
      ];
    }
    else
    {
      return $files;
    }
  }

}
