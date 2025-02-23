<?php
namespace Core;


class Error {

  /**
   * result
   * @param string $message
   * @param int $code
   * @return object
   */
  public static function result(string $message = 'Unknown error', int $code = 500): object
  {
    return Output::result((object)[
      'code' => $code,
      'message' => $message,
    ]);
  }

  public static function raw(int $code): void
  {
    switch ($code)
    {
      case 404:
        header('HTTP/1.0 404 Not Found');
        break;
      default:
        header('HTTP/1.1 500 Internal Server Error');
        break;
    }
  }

}
