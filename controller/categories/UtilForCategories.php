<?php
namespace Controller\categories;
use Core, Controller;

/**
 * util for categories
 */

class UtilForCategories {

  /**
   * extend article count
   *
   * @param Core\Goose|Core\Connect $self
   * @param string $where
   * @param array $index
   * @return array
   */
  public static function extendArticleCountInItems($self, string $where, array $index)
  {
    foreach ($index as $k=>$v)
    {
      $cnt = $self->model->getCount((object)[
        'table' => 'articles',
        'where' => $where.' and category_srl='.(int)$v->srl,
      ]);
      $index[$k]->count_article = $cnt->data;
    }
    return $index;
  }

  /**
   * extend all item
   *
   * @param Core\Goose|Core\Connect $self
   * @param string $where
   * @param array $index
   * @param int $nest_srl
   * @return array
   */
  public static function extendAllArticlesInItems($self, string $where, array $index, int $nest_srl)
  {
    // set item
    $item = (object)[
      'srl' => '',
      'nest_srl' => $nest_srl,
      'name' => 'All',
    ];

    // get article count
    if (Core\Util::checkKeyInExtField('count_article', $self->get->ext_field))
    {
      $where .= $nest_srl ? ' and nest_srl='.$nest_srl : '';
      $cnt = $self->model->getCount((object)[
        'table' => 'articles',
        'where' => $where,
      ]);
      $item->count_article = $cnt->data;
    }

    // add item
    array_unshift($index, $item);

    return $index;
  }

  /**
   * extend none item
   *
   * @param Core\Goose|Core\Connect $self
   * @param string $where
   * @param array $index
   * @param int $nest_srl
   * @return array
   */
  public static function extendNoneArticleInItems($self, string $where, array $index, int $nest_srl)
  {
    // set item
    $item = (object)[
      'srl' => 'null',
      'nest_srl' => $nest_srl,
      'name' => 'none',
    ];
    if (Core\Util::checkKeyInExtField('count_article', $self->get->ext_field))
    {
      $where .= $nest_srl ? ' and nest_srl='.$nest_srl : '';
      $where .= ' and category_srl IS NULL';
      $cnt = $self->model->getCount((object)[
        'table' => 'articles',
        'where' => $where,
      ]);
      $item->count_article = $cnt->data;
    }
    // add item
    array_push($index, $item);

    return $index;
  }

  /**
   * extend item
   * 목록에 대한 확장기능
   *
   * @param Core\Goose|Core\Connect $self
   * @param object $token
   * @param array $index
   * @param int $nest_srl
   * @return array
   */
  public static function extendItems($self, object $token, array $index, int $nest_srl)
  {
    if (!(isset($index) && count($index) > 0)) return [];

    // set common where
    $where = '';
    if (isset($token->data->user_srl) && !$token->data->admin)
    {
      $where .= ' and user_srl='.$token->data->user_srl;
    }
    $where .= Controller\articles\UtilForArticles::getWhereType('all');

    // get article count
    if (Core\Util::checkKeyInExtField('count_article', $self->get->ext_field))
    {
      $index = self::extendArticleCountInItems($self, $where, $index);
    }
    // get all item
    if (Core\Util::checkKeyInExtField('item_all', $self->get->ext_field))
    {
      $index = self::extendAllArticlesInItems($self, $where, $index, $nest_srl);
    }
    // get none category
    if (Core\Util::checkKeyInExtField('none', $self->get->ext_field))
    {
      $index = self::extendNoneArticleInItems($self, $where, $index, $nest_srl);
    }

    return $index;
  }

}
