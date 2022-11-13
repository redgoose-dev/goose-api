<?php
namespace Controller\json;
use Core\Goose, Core\Connect;
use Controller;

/**
 * util for json
 */

class UtilForJson {

  /**
   * extend category name in items
   */
  public static function extendCategoryNameInItems(Goose|Connect $self, array $index): array
  {
    if (count($index ?? []) <= 0) return [];
    foreach ($index as $k => $v)
    {
      if (!($v->category_srl ?? false))
      {
        $v->category_name = '';
        continue;
      }
      $category = $self->model->getItem((object)[
        'table' => 'categories',
        'field' => 'name',
        'where' => 'srl='.(int)$v->category_srl,
      ])->data;
      $v->category_name = $category->name ?? '';
    }
    return $index;
  }

  /**
   * extend category name in item
   */
  public static function extendCategoryNameInItem(Goose|Connect $self, object $item): object
  {
    $category = $self->model->getItem((object)[
      'table' => 'categories',
      'field' => 'name',
      'where' => 'srl='.(int)$item->category_srl,
      'debug' => __API_DEBUG__,
    ])->data;
    if ($category->name ?? false)
    {
      $item->category_name = $category->name;
    }
    return $item;
  }

}