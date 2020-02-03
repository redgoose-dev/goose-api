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
          unlink(__PATH__.'/'.$article->data->json->thumbnail->path);
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
   * @param int $srl
   * @throws Exception
   */
  public static function removeAttachFiles($model, $srl)
  {
    try
    {
      // set where
      $where = 'article_srl='.$srl;

      // remove files
      $files = $model->getItems((object)[
        'table' => 'files',
        'where' => $where,
      ]);
      if ($files->data && count($files->data))
      {
        foreach ($files->data as $k=>$v)
        {
          if (isset($v->loc) && $v->loc && file_exists(__PATH__.'/'.$v->loc))
          {
            unlink(__PATH__.'/'.$v->loc);
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

}
