<?php
namespace Core;
use Exception;

/**
 * Goose
 * 인스톨이 된 상태에서 진행되는 행동에 관한 클래스.
 * - 설정파일 불러오기
 * - 토큰 검사
 * - 라우터 초기화
 * - url 라우트에 의한 컨트롤러 실행
 *
 * @property Router router
 * @property string target
 * @property array params
 * @property Model model
 */

class Goose {

  public function __construct()
  {
    $this->router = new Router();
    $this->target = null;
    $this->params = null;
    $this->model = null;
  }

  /**
   * Play app trigger
   *
   * @throws Exception
   */
  public function run()
  {
    // initialize routing
    $this->router->init($_ENV['API_PATH_RELATIVE']);
    $this->router->match();

    // check router match
    if (!$this->router->match)
    {
      return Error::data(Message::make('msg.notFoundMatch'), 404);
    }

    // set router values
    $this->target = $this->router->match['target'];
    $this->params = $this->router->match['params'];

    // set model
    $this->model = new Model();

    // run turning point
    try
    {
      require Util::getControllerPath($this->target);
    }
    catch(Exception $e)
    {
      Error::data($e->getMessage(), $e->getCode());
    }
  }

}
