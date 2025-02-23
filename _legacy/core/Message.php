<?php
namespace Core;
use Exception;

class Message {

  /**
   * import json
   * @throws Exception
   */
  static private function import(string $code = 'en'): array
  {
    try
    {
      $json = file_get_contents(__API_PATH__.'/resource/message/'.$code.'.json');
      return (array)json_decode($json, true);
    }
    catch(Exception $e)
    {
      throw new Exception($e);
    }
  }

  /**
   * block
   */
  static private function block(array $tree, array $path): string
  {
    $temp = &$tree;
    foreach($path as $key)
    {
      $temp = &$temp[$key];
    }
    return $temp ?? '';
  }

  /**
   * find
   */
  static private function find(array $path): string
  {
    try
    {
      $value = self::block(self::import($_ENV['API_LANGUAGE']), $path);
      if (!$value && $_ENV['API_LANGUAGE'] !== 'en')
      {
        $value = self::block(self::import(), $path);
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
   */
  static public function make(string $path, ...$args): string
  {
    $paths = explode('.', $path);
    $value = self::find($paths);
    return Text::printf($value, ...$args);
  }

  /**
   * get error upload file message
   */
  static public function errorUploadFile(int $code = 0): string
  {
    return match ($code)
    {
      UPLOAD_ERR_INI_SIZE => self::make('file.UPLOAD_ERR_INI_SIZE'),
      UPLOAD_ERR_FORM_SIZE => self::make('file.UPLOAD_ERR_FORM_SIZE'),
      UPLOAD_ERR_PARTIAL => self::make('file.UPLOAD_ERR_PARTIAL'),
      UPLOAD_ERR_NO_FILE => self::make('file.UPLOAD_ERR_NO_FILE'),
      UPLOAD_ERR_NO_TMP_DIR => self::make('file.UPLOAD_ERR_NO_TMP_DIR'),
      UPLOAD_ERR_CANT_WRITE => self::make('file.UPLOAD_ERR_CANT_WRITE'),
      UPLOAD_ERR_EXTENSION => self::make('file.UPLOAD_ERR_EXTENSION'),
      default => self::make('file.UPLOAD_ERR_OK'),
    };
  }

}
