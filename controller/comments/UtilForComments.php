<?php
namespace Controller\comments;
use Exception, Core, Controller;

/**
 * util for articles
 */

class UtilForComments {

  /**
   * check data
   *
   * @param Core\Model $model
   * @param int $srl
   * @param string $table
   * @param string $label
   * @throws Exception
   */
  public static function checkData($model, $srl, $table, $label)
  {
    if ($srl && (int)$srl > 0)
    {
      $cnt = $model->getCount((object)[
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
   * @param Core\Model $model
   * @param array $index
   * @return array
   * @throws Exception
   */
  public static function getUserName($model, $index)
  {
    if (!(isset($index) && count($index))) return [];
    foreach ($index as $k=>$v)
    {
      $user = $model->getItem((object)[
        'table' => 'users',
        'field' => 'name',
        'where' => 'srl='.(int)$v->user_srl,
      ]);
      $index[$k]->user_name = isset($user->data->name) ? $user->data->name : Core\Message::make('word.anonymous');
    }
    return $index;
  }

}