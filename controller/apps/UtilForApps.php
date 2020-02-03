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
   * @param Core\Model $model
   * @param array $index
   * @return array
   * @throws
   */
  public static function getCountNests($model, $index)
  {
    foreach ($index as $k=>$v)
    {
      $index[$k]->count_nest = $model->getCount((object)[
        'table' => 'nests',
        'where' => 'app_srl='.(int)$v->srl,
      ])->data;
    }
    return $index;
  }

}