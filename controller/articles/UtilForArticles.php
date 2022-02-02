<?php
namespace Controller\articles;
use Core\Goose, Core\Connect;
use Controller, Controller\Main;
use Exception;

/**
 * util for articles
 */

class UtilForArticles {

  /**
   * get next page number
   */
  public static function getNextPage(Goose|Connect $self, ?string $where = ''): int
  {
    try
    {
      $self->get->page = ($self->get->page ?? 1) + 1;
      $self->get->field = 'srl';
      // get items
      $next_output = Main::index($self, (object)[
        'table' => 'articles',
        'field' => 'srl',
        'where' => $where,
      ]);
      return (count($next_output->data->index ?? []) > 0) ? (int)$self->get->page : 0;
    }
    catch(Exception)
    {
      return 0;
    }
  }

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
   * extend nest name in items
   */
  public static function extendNestNameInItems(Goose|Connect $self, array $index): array
  {
    if (count($index ?? []) <= 0) return [];
    foreach ($index as $k => $v)
    {
      if (!($v->nest_srl ?? false)) continue;
      $nest = $self->model->getItem((object)[
        'table' => 'nests',
        'field' => 'name',
        'where' => 'srl='.(int)$v->nest_srl,
      ]);
      if ($nest->data->name ?? false)
      {
        $v->nest_name = $nest->data->name;
      }
    }
    return $index;
  }

  /**
   * type 값 구분용 where 쿼리 만들기
   */
  public static function getWhereType(?string $type): string
  {
    // 모든 글 가져오기
    if ($type === 'all')
    {
      return ' and NOT type LIKE \'ready\'';
    }
    // 특정 type 글 가져오기
    else if ($type)
    {
      return match ($type) {
        'ready', 'private' => ' and type LIKE \''.$type.'\'',
        default => ' and type LIKE \'public\'',
      };
    }
    // 공개된 글만 가져오기
    else
    {
      return ' and type LIKE \'public\'';
    }
  }

  /**
   * get post type
   *
   * @param string $type private,public
   * @return string
   */
  public static function getPostType(string $type = 'public'): string
  {
    return match ($type) {
      'private' => 'private',
      default => 'public',
    };
  }

  /**
   * check order date
   */
  public static function checkOrderDate(?string $date = ''): bool
  {
    return preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date) > 0;
  }

}
