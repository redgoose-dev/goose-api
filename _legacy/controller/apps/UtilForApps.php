<?php
namespace Controller\apps;
use Core\Goose, Core\Connect;

/**
 * util for apps
 */

class UtilForApps {

  /**
   * get count nests
   *
   * @param Goose|Connect $self
   * @param array $index
   * @return array
   * @throws
   */
  public static function getCountNests(Goose|Connect $self, array $index): array
  {
    foreach ($index as $k=>$v)
    {
      $v->count_nest = $self->model->getCount((object)[
        'table' => 'nests',
        'where' => 'app_srl='.(int)$v->srl,
      ])->data;
    }
    return $index;
  }

}