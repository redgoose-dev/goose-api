<?php
namespace Core;
use Exception;

/**
 * Goose connect
 *
 * @property bool $loaded
 * @property Model $model
 * @property Router $router
 * @property string $target
 * @property array $params
 * @property object $get
 * @property object $post
 * @property array $files
 */
class Connect {

  private bool $loaded = false;
  private Router $router;
  public Model $model;
  public string $target;
  public array $params;
  public ?object $get;
  public ?object $post;
  public ?array $files;

  /**
   * Initialize class
   * 클래스 초기화할때 실행한다.
   * 이 객체로 여러번 사용하기 위하여 번거로운 값 보내줘야하는 일들을 넘기기 위하여 이 메서드를 실행시키게 되었다.
   *
   * @param object $options
   * @return void
   * @throws Exception
   */
  public function init(object $options): void
  {
    // check and set loaded
    if ($this->loaded) throw new Exception('Loaded connect');
    $this->loaded = true;

    // check and set token
    if (!($options->token ?? false)) throw new Exception('not found token');
    define('__API_TOKEN__', $options->token);

    // check install
    // TODO: 일단 사용안해보자..
    // Install::check();

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
   * @param array|null $files
   * @return object|string
   */
  public function request(string $method='get', string $path='/', object $query=null, array $files=null): object|string
  {
    try
    {
      // set route match
      $this->router->match($path, $method);
      if (!$this->router->match)
      {
        throw new Exception(Message::make('msg.notFoundMatch'), 404);
      }

      // set router values
      $this->target = $this->router->match['target'];
      $this->params = $this->router->match['params'];

      // convert $_GET and $_POST
      $this->get = $query->get ?? (object)[];
      $this->post = $query->post ?? (object)[];
      $this->files = $query->files ?? [];

      // controller router
      $requirePath = Util::controllerRouter($this->target);

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
