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
 */
class Goose {

  public Router $router;
  public string $target;
  public array $params;
  public Model $model;
  public object $get;
  public object $post;
  public array $files;

  public function __construct()
  {
    $this->router = new Router();
  }

  /**
   * Play app trigger
   *
   * @throws Exception
   */
  public function run(): void
  {
    // initialize routing
    $this->router->init($_ENV['API_PATH_RELATIVE'] ?? null);
    $this->router->match();

    // check router match
    if (!$this->router->match)
    {
      Error::result(Message::make('msg.notFoundMatch'), 404);
    }

    // set router values
    $this->target = $this->router->match['target'];
    $this->params = $this->router->match['params'];

    // convert $_GET and $_POST
    $this->get = (object)$_GET;
    $this->post = (object)$_POST;
    $this->files = (array)$_FILES;

    // set model
    $this->model = new Model();

    try
    {
      // run controller
      require_once Util::controllerRouter($this->target);
    }
    catch(Exception $e)
    {
      Error::result($e->getMessage(), $e->getCode());
    }
  }

}
