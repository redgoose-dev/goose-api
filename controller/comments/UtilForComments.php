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

}