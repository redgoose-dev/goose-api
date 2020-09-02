<?php
namespace Controller\articles;
use Exception, Core, Controller;

/**
 * util for articles
 */

class UtilForArticles {

  /**
   * get next page number
   *
   * @param Core\Model|Core\Connect $self
   * @param string $where
   * @return int
   * @throws
   */
  public static function getNextPage($self, string $where='')
  {
    try
    {
      if (!$self->get->page) $self->get->page = 1;
      $self->get->page = (int)$self->get->page + 1;
      $self->get->field = 'srl';
      // get items
      $next_output = Controller\Main::index($self, (object)[
        'table' => 'articles',
        'field' => 'srl',
        'where' => $where,
      ]);
      if ($next_output->data && $next_output->data->index && count($next_output->data->index))
      {
        return (int)$self->get->page;
      }
      return null;
    }
    catch(Exception $e)
    {
      return null;
    }
  }

  /**
   * extend category name in items
   *
   * @param Core\Goose|Core\Connect $self
   * @param array $index
   * @return array
   */
  public static function extendCategoryNameInItems($self, array $index)
  {
    if (!(isset($index) && count($index))) return [];
    foreach ($index as $k=>$v)
    {
      if (!$v->category_srl)
      {
        $index[$k]->category_name = null;
        continue;
      }
      $category = $self->model->getItem((object)[
        'table' => 'categories',
        'field' => 'name',
        'where' => 'srl='.(int)$v->category_srl,
      ]);
      $index[$k]->category_name = isset($category->data->name) ? $category->data->name : null;
    }
    return $index;
  }

  /**
   * extend nest name in items
   *
   * @param Core\Goose|Core\Connect $self
   * @param array $index
   * @return array
   */
  public static function extendNestNameInItems($self, array $index)
  {
    if (!(isset($index) && count($index))) return [];
    foreach ($index as $k=>$v)
    {
      if (!$v->nest_srl) continue;
      $nest = $self->model->getItem((object)[
        'table' => 'nests',
        'field' => 'name',
        'where' => 'srl='.(int)$v->nest_srl,
      ]);
      if ($nest->data && $nest->data->name)
      {
        $index[$k]->nest_name = $nest->data->name;
      }
    }
    return $index;
  }

  /**
   * type 값 구분용 where 쿼리 만들기
   *
   * @param string|null $type
   * @return string
   */
  public static function getWhereType(string $type=null)
  {
    // 모든 글 가져오기
    if ($type === 'all')
    {
      return ' and NOT type LIKE \'ready\'';
    }
    // 특정 type 글 가져오기
    else if ($type)
    {
      switch ($type)
      {
        case 'ready':
        case 'private':
          return ' and type LIKE \''.$type.'\'';
        default:
          return ' and type LIKE \'public\'';
      }
    }
    // 공개된 글만 가져오기
    else
    {
      return ' and type LIKE \'public\'';
    }
  }

}
