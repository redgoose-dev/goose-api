<?php
namespace Controller\apps;
use Exception, Core;

/**
 * util for apps
 */

class UtilForApps {

  /**
   * get count nests
   *
   * @param Core\Goose|Core\Connect $self
   * @param array $index
   * @return array
   * @throws
   */
  public static function getCountNests($self, array $index)
  {
    foreach ($index as $k=>$v)
    {
      $index[$k]->count_nest = $self->model->getCount((object)[
        'table' => 'nests',
        'where' => 'app_srl='.(int)$v->srl,
      ])->data;
    }
    return $index;
  }

}