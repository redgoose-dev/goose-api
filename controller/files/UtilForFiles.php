<?php
namespace Controller\files;
use Exception, Core;

/**
 * util for files
 */

class UtilForFiles {

  /**
   * remove thumbnail image
   *
   * @param Core\Model $model
   * @param int $article_srl
   * @throws Exception
   */
  public static function removeThumbnailImage($model, $article_srl)
  {
    try
    {
      // remove thumbnail image
      $article = $model->getItem((object)[
        'table' => 'articles',
        'where' => 'srl='.$article_srl,
        'json_field' => ['json'],
      ]);
      if ($article->data->json->thumbnail && $article->data->json->thumbnail->path)
      {
        if (file_exists($article->data->json->thumbnail->path))
        {
          unlink(__API_PATH__.'/'.$article->data->json->thumbnail->path);
        }
      }
    }
    catch(Exception $e)
    {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * Remove attach files
   *
   * @param Core\Model $model
   * @param int $target_srl
   * @param int $module
   * @throws Exception
   */
  public static function removeAttachFiles($model, $target_srl, $module)
  {
    try
    {
      // set where
      $where = 'target_srl='.$target_srl.' and module LIKE \''.$module.'\'';

      // remove files
      $files = $model->getItems((object)[
        'table' => 'files',
        'where' => $where,
      ]);
      if ($files->data && count($files->data))
      {
        foreach ($files->data as $k=>$v)
        {
          if (isset($v->path) && $v->path && file_exists(__API_PATH__.'/'.$v->path))
          {
            unlink(__API_PATH__.'/'.$v->path);
          }
        }
        // remove db
        $model->delete((object)[
          'table' => 'files',
          'where' => $where,
        ]);
      }
    }
    catch(Exception $e)
    {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * check target data
   *
   * @param Core\Model $model
   * @param int $target_srl
   * @param string $module
   * @param object $token
   * @throws Exception
   */
  public static function checkTargetData($model, $target_srl, $module, $token)
  {
    $where = 'srl='.$target_srl;
    $where .= (!$token->data->admin) ? ' and user_srl='.(int)$token->data->user_srl : '';
    $cnt = $model->getCount((object)[
      'table' => $module,
      'where' => $where,
    ]);
    if ($cnt->data <= 0)
    {
      throw new Exception(Core\Message::make('error.notInData', 'target_srl', $module));
    }
  }

}
