<?php
namespace Core;

/**
 * Output
 * 결과물을 만들어서 출력하거나 내보내는 역할을 한다.
 */
class Output {

  /**
   * get result data
   *
   * @param object|array $result
   * @return object
   */
  private static function getResultData(object|array $result): object
  {
    if ($result)
    {
      // filtering query
      if (!__API_DEBUG__) unset($result->query);

      // set success
      $result->success = false;

      // filtering code
      switch ($result->code = $result->code ?? 500)
      {
        case 204:
          $result->message = $result->message ?? 'Unknown error';
          break;
        case 401:
          $result->message = (isset($result->message) && __API_DEBUG__) ? $result->message : 'Authorization error';
          break;
        case 403:
          $result->message = (isset($result->message) && __API_DEBUG__) ? $result->message : 'Permission denied';
          break;
        case 404:
          $result->message = (isset($result->message) && __API_DEBUG__) ? $result->message : 'Not found data';
          break;
        case 200:
          $result->success = true;
          break;
        default:
          $result->code = 500;
          $result->message = (isset($result->message) && __API_DEBUG__) ? $result->message : 'Service error';
          break;
      }
    }
    else
    {
      $result = (object)[
        'code' => 500,
        'message' => 'Service error',
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

  /**
   * result data
   * api 모드일때 리턴하지않고 종료한다.
   *
   * @param object|array $result
   * @return object
   */
  public static function result(object|array $result = null): ?object
  {
    // set result
    $result = self::getResultData($result);
    // switching mode
    switch (__API_MODE__)
    {
      case 'library':
        return $result;
      default:
        echo json_encode(
          $result,
          !isset($_GET['min']) ? JSON_PRETTY_PRINT : null
        );
        exit;
    }
  }

}
