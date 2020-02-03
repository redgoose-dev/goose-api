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

}
