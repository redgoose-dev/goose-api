<?php
namespace Core;
use AltoRouter, Exception;


/**
 * Router
 *
 * @property array match
 */

class Router {

  private AltoRouter $core;
  public ?array $match;

  /**
   * get route map
   *
   * @return array
   */
  private function map()
  {
    return require __DIR__.'/../bootstrap/route.php';
  }

  /**
   * @param string $basePath
   * @throws Exception
   */
  public function init($basePath='')
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
  public function match($path=null, $method=null)
  {
    $match = $this->core->match($path, $method);
    $this->match = $match ? $match : null;
  }

}
