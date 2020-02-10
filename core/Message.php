<?php
namespace Core;
use Exception;

class Message {

  /**
   * import json
   *
   * @param string $code
   * @return object
   * @throws Exception
   */
  static private function import($code)
  {
    try
    {
      $json = file_get_contents(__PATH__.'/resource/message/'.$code.'.json');
      return json_decode($json, true);
    }
    catch(Exception $e)
    {
      throw new Exception($e);
    }
  }

  /**
   * find
   *
   * @param array $path
   * @return string
   */
  static private function find($path)
  {
    function block($tree, $path)
    {
      $temp = &$tree;
      foreach($path as $key)
      {
        $temp = &$temp[$key];
      }
      return $temp ? $temp : '';
    }

    try
    {
      $value = block(self::import($_ENV['LANGUAGE']), $path);
      if (!$value && $_ENV['LANGUAGE'] !== 'en')
      {
        $value = block(self::import('en'), $path);
      }
      return $value;
    }
    catch(Exception $e)
    {
      return '';
    }
  }

  /**
   * make
   * example) Message::make('error.notFound', 'srl');
   *
   * @param string $path
   * @param array $args
   * @return string
   */
  static public function make($path, ...$args)
  {
    $value = self::find(explode('.', $path));
    return Text::printf($value, ...$args);
  }

  /**
   * get error upload file message
   *
   * @param int $code
   * @return string
   */
  static public function errorUploadFile($code)
  {
    switch ($code)
    {
      case UPLOAD_ERR_INI_SIZE:
        return self::make('file.UPLOAD_ERR_INI_SIZE');
      case UPLOAD_ERR_FORM_SIZE:
        return self::make('file.UPLOAD_ERR_FORM_SIZE');
      case UPLOAD_ERR_PARTIAL:
        return self::make('file.UPLOAD_ERR_PARTIAL');
      case UPLOAD_ERR_NO_FILE:
        return self::make('file.UPLOAD_ERR_NO_FILE');
      case UPLOAD_ERR_NO_TMP_DIR:
        return self::make('file.UPLOAD_ERR_NO_TMP_DIR');
      case UPLOAD_ERR_CANT_WRITE:
        return self::make('file.UPLOAD_ERR_CANT_WRITE');
      case UPLOAD_ERR_EXTENSION:
        return self::make('file.UPLOAD_ERR_EXTENSION');
      case UPLOAD_ERR_OK:
      default:
        return self::make('file.UPLOAD_ERR_OK');
    }
  }

}
