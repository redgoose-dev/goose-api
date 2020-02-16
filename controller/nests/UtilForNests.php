<?php
namespace Controller\nests;
use Exception, Core, Controller;

/**
 * util for nests
 */

class UtilForNests {

  /**
   * get count articles
   *
   * @param Core\Model $model
   * @param array $index
   * @param object $token
   * @return array
   * @throws Exception
   */
  public static function getCountArticles($model, $index, $token)
  {
    $whereBase = '';
    if (isset($token->data->user_srl) && !$token->data->admin)
    {
      $whereBase .= ' and user_srl='.$token->data->user_srl;
    }
    $whereBase .= Controller\articles\UtilForArticles::getWhereType('all');
    foreach ($index as $k=>$v)
    {
      $index[$k]->count_article = $model->getCount((object)[
        'table' => 'articles',
        'where' => 'nest_srl='.(int)$v->srl.$whereBase,
      ])->data;
    }
    return $index;
  }

}