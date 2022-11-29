<?php
namespace Core;
use Exception;

/**
 * File
 */

class File {

  /**
   * make directory
   */
  public static function makeDirectory(string $path, int $permission = 0707): void
  {
    if (is_dir($path)) return;
    $umask = umask();
    umask(000);
    mkdir($path, $permission);
    umask($umask);
  }

  /**
   * check filename
   */
  public static function checkFilename(string $name, bool $useRandomText): string
  {
    if (!$name) return '';
    // set allow file type
    $allowFileType = $_ENV['API_FILE_ALLOW_TYPE'];
    $allowFileType = explode(',', $allowFileType);
    // set source
    $src = [
      basename($name, strrchr($name, '.')),
      strtolower(substr(strrchr($name, '.'), 1))
    ];
    // check file type
    if (!in_array($src[1], $allowFileType)) return '';
    // only eng or number
    $src[0] = preg_replace("/[^A-Za-z0-9-_]+/", '-', $src[0]);
    // remove special characters
    $src[0] = Text::removeSpecialChar($src[0]);
    // make random name
    if (!$src[0] || $useRandomText)
    {
      $src[0] = md5(date('YmdHis') . '-' . rand());
    }
    // return
    return $src[0] . '.' . $src[1];
  }

  /**
   * check exist file
   * 파일이름이 같은것이 있다면 이름뒤에 "-{x}"키워드를 붙인다.
   * 중복되는 이름이 있다면 x값을 올려서 붙인다.
   */
  public static function checkExistFile(string $dir, string $file, int|null $n): string
  {
    if (!$file) return '';
    // set values
    if (is_null($n))
    {
      $n = 0;
      $new = $file;
    }
    else
    {
      $n = $n + 1;
      $new = basename($file, strrchr($file, '.'))."-$n.".substr(strrchr($file, '.'), 1);
    }
    // check exist
    return (file_exists($dir . $new)) ? self::checkExistFile($dir, $file, $n) : $new;
  }

  /**
   * get directories
   */
  public static function getDirectories(string $path): array
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
   */
  public static function getFiles(string $path): array
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
   * `$_FILES`에 들어있는 값을 multiple 형태로 변환시켜서 구조를 통일화시킨다.
   */
  public static function convertFilesValue(array $files): array
  {
    return (isset($files['name']) && is_array($files['name'])) ? [
      'name' => $files['name'][0],
      'type' => $files['type'][0],
      'tmp_name' => $files['tmp_name'][0],
      'size' => $files['size'][0],
      'error' => $files['error'][0],
    ] : $files;
  }

  /**
   * get mime type
   */
  public static function getMimeType(string $path): string
  {
    if (!is_file($path)) return '';
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($fileInfo)
    {
      $type = finfo_file($fileInfo, $path);
      finfo_close($fileInfo);
    }
    else if (function_exists('mime_content_type'))
    {
      $type = mime_content_type($path);
    }
    else
    {
      return '';
    }
    return $type;
  }

}
