<?php
namespace Core;


class Error {

  /**
   * data type error
   *
   * @param string $message
   * @param int $code
   * @return object
   */
  public static function data($message='Unknown error', $code=500)
  {
    return Output::data((object)[
      'code' => $code,
      'message' => $message,
    ]);
  }

}