<?php
namespace Controller\nests;
use Exception, Core;

/**
 * util for nests
 */

class UtilForNests {

  /**
   * get count articles
   *
   * @param Core\Model $model
   * @param array $index
   * @return array
   * @throws
   */
  public static function getCountArticles($model, $index)
  {
    foreach ($index as $k=>$v)
    {
      $index[$k]->count_article = $model->getCount((object)[
        'table' => 'articles',
        'where' => 'nest_srl='.(int)$v->srl,
      ])->data;
    }
    return $index;
  }

}