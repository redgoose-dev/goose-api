<?php
namespace Controller\files;
use Exception;
use Core, Core\Goose, Core\Connect, Core\File;

/**
 * util for files
 */

class UtilForFiles {

  public static string $upload = '/data/upload/';
  public static string $uploadFull = __API_PATH__.'/data/upload/';

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

  /**
   * create assets files map
   * /data/upload/{ASSETS}/map.json 파일을 만든다.
   */
  public static function createAssetsMapFile(string $dir): object|null
  {
    $pathMap = self::$uploadFull.$dir.'/map.json';
    // check map.json file
    if (file_exists($pathMap)) return null;
    // get files
    $tree = self::getAssetFiles($dir);
    // write file
    if ($tree) self::writeAssetsMapFile($tree, $dir);
    // return
    return $tree;
  }

  /**
   * get assets map files
   */
  public static function getAssetsMapFiles(string $dir): object|null
  {
    $pathMap = self::$uploadFull.$dir.'/map.json';
    try
    {
      if (!file_exists($pathMap)) return null;
      $json = file_get_contents($pathMap);
      return json_decode($json);
    }
    catch (Exception $e)
    {
      return null;
    }
  }

  /**
   * write assets map file
   */
  public static function writeAssetsMapFile(object $data, string $dir): void
  {
    $json = json_encode($data, true);
    $file = fopen(self::$uploadFull.$dir.'/map.json', 'w');
    fwrite($file, $json);
    fclose($file);
  }

  /**
   * get asset files
   */
  private static function getAssetFiles(string $dirName): object|null
  {
    $pathBase = self::$uploadFull.$dirName;
    if (!file_exists($pathBase)) return null;
    // get directories (`YYYYMM`형식으로된 이름)
    $directories = File::getDirectories($pathBase);
    // set files tree
    $tree = (object)[];
    foreach ($directories as $dir)
    {
      $files = File::getFiles($pathBase.'/'.$dir);
      if (!count($files)) continue;
      foreach ($files as $file)
      {
        $filePath = $pathBase.'/'.$dir.'/'.$file;
        $obj = (object)[
          'name' => $file,
          'size' => filesize($filePath),
          'date' => filemtime($filePath),
          'type' => File::getMimeType($filePath),
        ];
        if (str_starts_with($obj->type, 'image'))
        {
          list( $width, $height ) = getimagesize($filePath);
          $obj->image = (object)[ 'width' => $width, 'height' => $height ];
        }
        $tree->{$dir.'/'.$file} = $obj;
      }
    }
    return $tree;
  }

}
