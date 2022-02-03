<?php
namespace Controller\nests;
use Exception, Core, Controller;
use Core\Goose, Core\Connect;
use Controller\articles\UtilForArticles;

/**
 * util for nests
 */

class UtilForNests {

  /**
   * get count articles
   * @throws Exception
   */
  public static function getCountArticles(Goose|Connect $self, array $index, object $token): array
  {
    $whereBase = '';
    if (isset($token->data->srl) && !$token->data->admin)
    {
      $whereBase .= ' and user_srl='.$token->data->srl;
    }
    $whereBase .= UtilForArticles::getWhereType($self->get->visible_type ?? '');
    foreach ($index as $k=>$v)
    {
      $v->count_article = $self->model->getCount((object)[
        'table' => 'articles',
        'where' => 'nest_srl='.(int)$v->srl.$whereBase,
      ])->data;
    }
    return $index;
  }

  /**
   * get app name
   * @throws Exception
   */
  public static function getAppName(Goose|Connect $self, array $index): array
  {
    foreach ($index as $k=>$v)
    {
      if ($v->app_srl)
      {
        $v->app_name = $self->model->getItem((object)[
          'table' => 'apps',
          'field' => 'name',
          'where' => 'srl='.(int)$v->app_srl,
        ])->data->name;
      }
    }
    return $index;
  }

}
