<?php
namespace Controller\categories;
use Core\Util, Core\Goose, Core\Connect;
use Controller\articles\UtilForArticles;

/**
 * util articles for categories
 */

class UtilArticlesForCategories {

  /**
   * extend article count
   */
  public static function extendArticleCountInItems(Goose|Connect $self, string $where, array $index): array
  {
    foreach ($index as $k => $v)
    {
      $cnt = $self->model->getCount((object)[
        'table' => 'articles',
        'where' => $where.' and category_srl='.(int)$v->srl,
      ])->data;
      $v->count_article = $cnt;
    }
    return $index;
  }

  /**
   * extend all item
   */
  public static function extendAllArticlesInItems(Goose|Connect $self, string $where, array $index, int $target_srl): array
  {
    $ext_field = $self->get->ext_field ?? null;
    // set item
    $item = (object)[ 'srl' => '', 'target_srl' => $target_srl, 'name' => 'All' ];
    // get article count
    if (Util::checkKeyInExtField('count', $ext_field))
    {
      $where .= $target_srl ? ' and nest_srl='.$target_srl : '';
      $cnt = $self->model->getCount((object)[
        'table' => 'articles',
        'where' => $where,
      ])->data;
      $item->count_article = $cnt;
    }
    // add item
    array_unshift($index, $item);
    // return
    return $index;
  }

  /**
   * extend none item
   */
  public static function extendNoneArticleInItems(Goose|Connect $self, string $where, array $index, int $target_srl): array
  {
    // set item
    $item = (object)[ 'srl' => 'null', 'target_srl' => $target_srl, 'name' => 'none' ];
    // get article count
    if (Util::checkKeyInExtField('count', $self->get->ext_field))
    {
      $where .= $target_srl ? ' and nest_srl='.$target_srl : '';
      $where .= ' and category_srl IS NULL';
      $cnt = $self->model->getCount((object)[
        'table' => 'articles',
        'where' => $where,
      ])->data;
      $item->count_article = $cnt;
    }
    // add item
    $index[] = $item;
    // return
    return $index;
  }

  /**
   * extend get articles count
   */
  public static function extendCountInItem(Goose|Connect $self, object $token, int $target_srl): int
  {
    $where = (!$token->data->admin && $token->data->srl) ? ' and user_srl='.(int)$token->data->srl : '';
    $where .= UtilForArticles::getWhereType($self->get->visible_type ?? null);
    return $self->model->getCount((object)[
      'table' => 'articles',
      'where' => $where.' and category_srl='.$target_srl,
    ])->data;
  }

  /**
   * extend item
   * 목록에 대한 확장기능
   */
  public static function extendItems(Goose|Connect $self, object $token, array $index, int $target_srl): array
  {
    if (count($index ?? []) <= 0) return [];

    // set common where
    $ext_field = $self->get->ext_field ?? null;
    $where = '';
    if (isset($token->data->srl) && !$token->data->admin)
    {
      $where .= ' and user_srl='.$token->data->srl;
    }
    $where .= UtilForArticles::getWhereType($self->get->visible_type ?? null);
    if ($q = ($self->get->q ?? null))
    {
      $where .= ' and (title LIKE \'%'.$q.'%\' or content LIKE \'%'.$q.'%\')';
    }

    // get article count
    if (Util::checkKeyInExtField('count', $ext_field))
    {
      $index = self::extendArticleCountInItems($self, $where, $index);
    }
    // get all items
    if (Util::checkKeyInExtField('all', $ext_field))
    {
      $index = self::extendAllArticlesInItems($self, $where, $index, $target_srl);
    }
    // get none item
    if (Util::checkKeyInExtField('none', $ext_field))
    {
      $index = self::extendNoneArticleInItems($self, $where, $index, $target_srl);
    }

    return $index;
  }

}
