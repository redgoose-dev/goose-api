<?php
namespace Core;


class Error {

  /**
   * result
   *
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

}