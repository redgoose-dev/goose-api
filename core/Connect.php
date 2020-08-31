<?php
namespace Core;
use Exception, AltoRouter;


/**
 * Goose connect
 *
 * @property bool $loaded
 */

class Connect {

  private bool $loaded = false;
  private Model $model;
  private Router $router;
  private string $target;
  private array $params;
  private ?object $_get;
  private ?object $_post;

  /**
   * Initialize class
   * 클래스 초기화할때 실행한다.
   * 이 객체로 여러번 사용하기 위하여 번거로운 값 보내줘야하는 일들을 넘기기 위하여 이 메서드를 실행시키게 되었다.
   *
   * @param object|null $options
   * @return void
   * @throws Exception
   */
  public function init(object $options=null)
  {
    // check and set loaded
    if ($this->loaded) throw new Exception('Loaded connect');
    $this->loaded = true;

    // check and set token
    if (!(isset($options->token) && $options->token)) throw new Exception('not found token');
    define('__API_TOKEN__', $options->token);

    // check install
    Install::check();

    // set model
    $this->model = new Model();

    // set router
    $this->router = new Router();
    $this->router->init();
  }

  /**
   * request
   * 모든 행동에 대한 요청 메서드
   *
   * @param string $method get,post
   * @param string $path
   * @param object|null $query
   * @return object
   */
  public function request($method='get', $path='/', object $query=null)
  {
    try
    {
      // set route match
      $this->router->match($path, $method);
      if (!$this->router->match)
      {
        throw new Exception(Message::make('msg.notFoundMatch'), 404);
      }
      $this->target = $this->router->match['target'];
      $this->params = $this->router->match['params'];

      // convert $_GET and $_POST
      // TODO: `$_GET,$_POST`값들을 변환 작업해줘야함

      // set controller path
      $requirePath = Util::getControllerPath($this->target);

      // play controller
      return require_once $requirePath;
    }
    catch(Exception $e)
    {
      return (object)[
        'success' => false,
        'code' => $e->getCode() && $e->getCode() > 0 ? $e->getCode() : 500,
        'message' => $e->getMessage(),
      ];
    }
  }

}