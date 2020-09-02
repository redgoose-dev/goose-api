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
   * @param Core\Goose|Core\Connect $self
   * @param int $article_srl
   * @throws Exception
   */
  public static function removeThumbnailImage($self, int $article_srl)
  {
    try
    {
      if (!$article_srl) throw new Exception();

      // remove thumbnail image
      $article = $self->model->getItem((object)[
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
   * @param Core\Goose|Core\Connect $self
   * @param int $target_srl
   * @param string $module
   * @throws Exception
   */
  public static function removeAttachFiles($self, int $target_srl, string $module)
  {
    try
    {
      if (!($target_srl && $module)) throw new Exception();

      // set where
      $where = 'target_srl='.$target_srl.' and module LIKE \''.$module.'\'';

      // remove files
      $files = $self->model->getItems((object)[
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
        $self->model->delete((object)[
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
   * @param Core\Goose|Core\Connect $self
   * @param int $target_srl
   * @param string $module
   * @param object $token
   * @throws Exception
   */
  public static function checkTargetData($self, int $target_srl, string $module, object $token)
  {
    $where = 'srl='.$target_srl;
    $where .= (!$token->data->admin) ? ' and user_srl='.(int)$token->data->user_srl : '';
    $cnt = $self->model->getCount((object)[
      'table' => $module,
      'where' => $where,
    ]);
    if ($cnt->data <= 0)
    {
      throw new Exception(Core\Message::make('error.notInData', 'target_srl', $module));
    }
  }

}
