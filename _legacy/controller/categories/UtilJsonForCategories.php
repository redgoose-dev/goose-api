<?php
namespace Controller\categories;
use Core\Util, Core\Goose, Core\Connect;
//use Controller\articles\UtilForJson;

/**
 * util json for categories
 */

class UtilJsonForCategories {

  /**
   * extend json count
   */
  public static function extendJsonCountInItems(Goose|Connect $self, string $where, array $index): array
  {
    foreach ($index as $k => $v)
    {
      $cnt = $self->model->getCount((object)[
        'table' => 'json',
        'where' => $where.' and category_srl='.(int)$v->srl,
      ])->data;
      $v->count_json = $cnt;
    }
    return $index;
  }

  /**
   * extend all item
   */
  public static function extendAllJsonInItems(Goose|Connect $self, string $where, array $index): array
  {
    $ext_field = $self->get->ext_field ?? null;
    // set item
    $item = (object)[ 'srl' => '', 'name' => 'All' ];
    // get json count
    if (Util::checkKeyInExtField('count', $ext_field))
    {
      $cnt = $self->model->getCount((object)[
        'table' => 'json',
        'where' => $where,
      ])->data;
      $item->count_json = $cnt;
    }
    // add item
    array_unshift($index, $item);
    // return
    return $index;
  }

  /**
   * extend none item
   */
  public static function extendNoneJsonInItems(Goose|Connect $self, string $where, array $index): array
  {
    // set item
    $item = (object)[ 'srl' => 'null', 'name' => 'none' ];
    // get json count
    if (Util::checkKeyInExtField('count', $self->get->ext_field))
    {
      $where .= ' and category_srl IS NULL';
      $cnt = $self->model->getCount((object)[
        'table' => 'json',
        'where' => $where,
      ])->data;
      $item->count_json = $cnt;
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
  public static function extendItems(Goose|Connect $self, object $token, array $index): array
  {
    if (count($index ?? []) <= 0) return [];

    // set common where
    $ext_field = $self->get->ext_field ?? null;
    $where = '';
    if (isset($token->data->srl) && !$token->data->admin)
    {
      $where .= ' and user_srl='.$token->data->srl;
    }
    if ($q = ($self->get->q ?? null))
    {
      $where .= ' and (title LIKE \'%'.$q.'%\' or content LIKE \'%'.$q.'%\')';
    }

    // get article count
    if (Util::checkKeyInExtField('count', $ext_field))
    {
      $index = self::extendJsonCountInItems($self, $where, $index);
    }
    // get all items
    if (Util::checkKeyInExtField('all', $ext_field))
    {
      $index = self::extendAllJsonInItems($self, $where, $index);
    }
    // get none item
    if (Util::checkKeyInExtField('none', $ext_field))
    {
      $index = self::extendNoneJsonInItems($self, $where, $index);
    }

    return $index;
  }

}