<?php
namespace Controller\comments;
use Core\Message, Core\Goose, Core\Connect;
use Controller;
use Exception;

/**
 * util for comments
 */

class UtilForComments {

  /**
   * check data
   * @throws Exception
   */
  public static function checkData(Goose|Connect $self, int $srl, string $table, string $label): void
  {
    if (($srl ?? 0) > 0)
    {
      $cnt = $self->model->getCount((object)[
        'table' => $table,
        'where' => 'srl='.$srl,
      ])->data;
      if ($cnt <= 0)
      {
        throw new Exception(Message::make('error.noDataValue', $label));
      }
    }
  }

  /**
   * get username
   * @throws Exception
   */
  public static function getUserName(Goose|Connect $self, array $index): array
  {
    if (count($index ?? []) <= 0) return [];
    foreach ($index as $k=>$v)
    {
      $user = $self->model->getItem((object)[
        'table' => 'users',
        'field' => 'name',
        'where' => 'srl='.(int)$v->user_srl,
      ]);
      $v->user_name = $user->data->name ?? Message::make('word.anonymous');
    }
    return $index;
  }

}
