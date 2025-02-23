<?php
namespace Core;
use AltoRouter, Exception;


/**
 * Router
 * 라우터 인터페이스 클래스
 *
 * @property array|bool match
 */

class Router {

  private AltoRouter $core;
  public array|bool $match;

  /**
   * get route map
   *
   * @return array
   */
  private function map(): array
  {
    return require __DIR__.'/../bootstrap/route.php';
  }

  /**
   * @param string $basePath
   * @throws Exception
   */
  public function init(string $basePath = ''): void
  {
    $this->core = new AltoRouter();
    $this->core->setBasePath($basePath);
    $this->core->addMatchTypes([ 'aa' => '[0-9A-Za-z_-]++' ]);
    $this->core->addRoutes($this->map());
  }

  /**
   * router match
   *
   * @param string|null $path
   * @param string|null $method
   */
  public function match(string|null $path = null, string|null $method = null): void
  {
    $this->match = $this->core->match($path, $method);
  }

}
