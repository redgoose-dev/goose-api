<?php
namespace Controller\files;
use Exception;
use Core, Core\Goose, Core\Connect;

/**
 * util for files
 */

class UtilForFiles {

  /**
   * remove thumbnail image
   *
   * @param Goose|Connect $self
   * @param int $article_srl
   * @throws Exception
   */
  public static function removeThumbnailImage(Goose|Connect $self, int $article_srl): void
  {
    try
    {
      if (!$article_srl) throw new Exception();
      // remove thumbnail image
      $article = $self->model->getItem((object)[
        'table' => 'articles',
        'where' => 'srl='.$article_srl,
        'json_field' => ['json'],
      ])->data;
      if ($path = $article->json->thumbnail->path ?? false)
      {
        if (file_exists($path))
        {
          unlink(__API_PATH__.'/'.$path);
        }
      }
    }
    catch(Exception $e)
    {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * remove file by path
   *
   * @param string $path
   * @throws Exception
   */
  public static function removeFileByPath(string $path): void
  {
    if (!($path ?? false)) return;
    try
    {
      if (file_exists($path)) unlink(__API_PATH__.'/'.$path);
    }
    catch (Exception $e)
    {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * Remove attach files
   *
   * @param Goose|Connect $self
   * @param int $target_srl
   * @param string $module
   * @throws Exception
   */
  public static function removeAttachFiles(Goose|Connect $self, int $target_srl, string $module): void
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
      if (count($files->data ?? 0) > 0)
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
      throw new Exception($e->getMessage(), $e->getCode());
    }
  }

  /**
   * check target data
   * @throws Exception
   */
  public static function checkTargetData(Goose|Connect $self, int $target_srl, string $module, object $token): void
  {
    $where = 'srl='.$target_srl;
    $where .= (!$token->data->admin) ? ' and user_srl='.(int)$token->data->srl : '';
    $cnt = $self->model->getCount((object)[
      'table' => $module,
      'where' => $where,
      'debug' => true,
    ])->data;
    if ($cnt <= 0)
    {
      throw new Exception(Core\Message::make('error.notInData', 'target_srl', $module));
    }
  }

}
