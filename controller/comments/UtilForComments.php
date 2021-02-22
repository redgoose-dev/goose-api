<?php
namespace Controller\comments;
use Exception, Core, Controller;

/**
 * util for comments
 */

class UtilForComments {

  /**
   * check data
   *
   * @param Core\Goose|Core\Connect $self
   * @param int $srl
   * @param string $table
   * @param string $label
   * @throws Exception
   */
  public static function checkData($self, int $srl, string $table, string $label)
  {
    if ($srl && (int)$srl > 0)
    {
      $cnt = $self->model->getCount((object)[
        'table' => $table,
        'where' => 'srl='.(int)$srl,
      ])->data;
      if ($cnt <= 0)
      {
        throw new Exception(Core\Message::make('error.noDataValue', $label));
      }
    }
  }

  /**
   * get user name
   *
   * @param Core\Goose|Core\Connect $self
   * @param array $index
   * @return array
   * @throws Exception
   */
  public static function getUserName($self, array $index)
  {
    if (!(isset($index) && count($index))) return [];
    foreach ($index as $k=>$v)
    {
      $user = $self->model->getItem((object)[
        'table' => 'users',
        'field' => 'name',
        'where' => 'srl='.(int)$v->user_srl,
      ]);
      $index[$k]->user_name = isset($user->data->name) ? $user->data->name : Core\Message::make('word.anonymous');
    }
    return $index;
  }

}