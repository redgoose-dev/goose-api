<?php
namespace Core;

class Output {

  /**
   * print data
   *
   * @param object|array $result
   * @return object|void
   */
  public static function data($result=null)
  {
    // set result
    $result = self::setResult($result);

    // print output
    switch (__API_MODE__)
    {
      case 'library':
        return $result;
      case 'api':
      default:
        echo json_encode(
          $result,
          !isset($_GET['min']) ? JSON_PRETTY_PRINT : null
        );
        exit;
    }
  }

  /**
   * set result
   *
   * @param object|array $result
   * @return object
   */
  private static function setResult($result)
  {
    if ($result)
    {
      // filtering query
      if (!__API_DEBUG__) unset($result->query);

      // set success
      $result->success = false;

      // filtering code
      switch ($result->code)
      {
        case 204:
          $result->message = $result->message ? $result->message : 'custom message';
          break;
        case 401:
          $result->message = ($result->message && __API_DEBUG__) ? $result->message : 'Authorization error';
          break;
        case 403:
          $result->message = ($result->message && __API_DEBUG__) ? $result->message : 'Permission denied';
          break;
        case 404:
          $result->message = ($result->message && __API_DEBUG__) ? $result->message : 'Not found data';
          break;
        case 200:
          $result->success = true;
          break;
        default:
          $result->code = 500;
          $result->message = ($result->message && __API_DEBUG__) ? $result->message : 'Service error';
          break;
      }
    }
    else
    {
      $result = (object)[
        'code' => 500,
        'message' => 'Service error'
      ];
    }

    // set url
    if (__API_DEBUG__ && __API_MODE__ === 'api')
    {
      $result->url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

    // set processing time
    if (__API_DEBUG__ && __API_START_TIME__)
    {
      $endTime = microtime(true);
      $time = $endTime - __API_START_TIME__;
      $result->time = number_format($time,6) * 1000 . 'ms';
    }

    return $result;
  }

}
