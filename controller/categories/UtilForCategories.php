<?php
namespace Controller\categories;
use Core\Util, Core\Goose, Core\Connect;
use Controller\articles\UtilForArticles;

/**
 * util for categories
 */

class UtilForCategories {

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
  public static function extendAllArticlesInItems(Goose|Connect $self, string $where, array $index, int $nest_srl): array
  {
    $ext_field = $self->get->ext_field ?? null;
    // set item
    $item = (object)[ 'srl' => '', 'nest_srl' => $nest_srl, 'name' => 'All' ];
    // get article count
    if (Util::checkKeyInExtField('count_article', $ext_field))
    {
      $where .= $nest_srl ? ' and nest_srl='.$nest_srl : '';
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
  public static function extendNoneArticleInItems(Goose|Connect $self, string $where, array $index, int $nest_srl): array
  {
    // set item
    $item = (object)[ 'srl' => 'null', 'nest_srl' => $nest_srl, 'name' => 'none' ];
    // get article count
    if (Util::checkKeyInExtField('count_article', $self->get->ext_field))
    {
      $where .= $nest_srl ? ' and nest_srl='.$nest_srl : '';
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
   * extend item
   * 목록에 대한 확장기능
   */
  public static function extendItems(Goose|Connect $self, object $token, array $index, int $nest_srl): array
  {
    if (count($index ?? []) <= 0) return [];

    // set common where
    $ext_field = $self->get->ext_field ?? null;
    $where = '';
    if (isset($token->data->srl) && !$token->data->admin)
    {
      $where .= ' and user_srl='.$token->data->srl;
    }
    if ($type = ($self->get->visible_type ?? null))
    {
      $where .= UtilForArticles::getWhereType($type);
    }
    if ($q = ($self->get->q ?? null))
    {
      $where .= ' and (title LIKE \'%'.$q.'%\' or content LIKE \'%'.$q.'%\')';
    }

    // get article count
    if (Util::checkKeyInExtField('count_article', $ext_field))
    {
      $index = self::extendArticleCountInItems($self, $where, $index);
    }
    // get all item
    if (Util::checkKeyInExtField('item_all', $ext_field))
    {
      $index = self::extendAllArticlesInItems($self, $where, $index, $nest_srl);
    }
    // get none category
    if (Util::checkKeyInExtField('none', $ext_field))
    {
      $index = self::extendNoneArticleInItems($self, $where, $index, $nest_srl);
    }

    return $index;
  }

}
