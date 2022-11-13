<?php
namespace Core;
use Exception, Controller\Main;
use Controller\files\UtilForFiles;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit file
 *
 * @var Goose|Connect $this
 */

try
{
  // check upload directories
  Util::checkDirectories();

  // check and set srl
  if (($srl = (int)($this->params['srl'] ?? 0)) <= 0)
  {
    throw new Exception(Message::make('error.notFound', 'srl'));
  }

  // `module`, `target_srl` 둘중 하나가 있다면 둘다 존재하는지 검사하기
  if ($this->post->target_srl ?? $this->post->module ?? false)
  {
    if (!(isset($this->post->target_srl) && isset($this->post->module)))
    {
      throw new Exception(Message::make('error.noItems_and', 'module', 'target_srl'));
    }
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Main::checkAccessItem($this, (object)[
    'table' => 'files',
    'srl' => $srl,
  ]);

  // `target_srl`값이 실제로 존재하는지 검사한다.
  if (isset($this->post->target_srl) && isset($this->post->module))
  {
    UtilForFiles::checkTargetData(
      $this,
      (int)$this->post->target_srl,
      $this->post->module,
      $token
    );
  }

  // $_FILES to array
  $file = File::convertFilesValue($this->files['file'] ?? []);

  // remove and upload file
  if ($file['name'] ?? false)
  {
    /**
     * upload new file
     */
    $month = date('Ym');
    $subDir = $this->post->sub_dir ?? $_ENV['API_DEFAULT_UPLOAD_DIR_NAME'];

    // set path
    $path = 'data/upload/'.$subDir;
    $path_absolute = __API_PATH__.'/'.$path;
    $path_absolute_dest = $path_absolute.'/'.$month;

    // make subdirectory
    File::makeDirectory($path_absolute, 0707);

    // make month directory
    File::makeDirectory($path_absolute_dest, 0777);

    // check file
    if (!!$file['error'])
    {
      throw new Exception(Message::errorUploadFile($file['error']));
    }

    // check file size
    if ((int)($file['size'] ?? 0) > (int)$_ENV['API_FILE_LIMIT_SIZE'])
    {
      throw new Exception(Message::make('error.limitFileSize'));
    }

    // check filename
    if (!($file['name'] = File::checkFilename($file['name'], false)))
    {
      throw new Exception(Message::make('error.allowFileType'));
    }

    // check exist filename
    $file['name'] = File::checkExistFile($path_absolute_dest.'/', $file['name'], null);

    // check path and tmp_name
    if (!($file['tmp_name'] && is_dir($path_absolute_dest)))
    {
      throw new Exception('upload error');
    }

    // copy file to target
    move_uploaded_file($file['tmp_name'], $path_absolute_dest.'/'.$file['name']);

    // set new file
    $newFile = (object)[
      'name' => $file['name'],
      'path' => $path.'/'.$month.'/'.$file['name'],
      'type' => $file['type'],
      'size' => $file['size'],
    ];

    /**
     * remove file
     */
    // get item
    $file = $this->model->getItem((object)[
      'table' => 'files',
      'field' => 'path',
      'where' => 'srl='.$srl,
    ])->data;

    // check exist file
    if (($file->path ?? false) && file_exists(__API_PATH__.'/'.$file->path))
    {
      @unlink(__API_PATH__.'/'.$file->path);
    }
  }
  else
  {
    $newFile = null;
  }

  // set data
  $data = [];
  if ($this->post->target_srl ?? false) $data[] = "target_srl={$this->post->target_srl}";
  if ($this->post->module ?? false) $data[] = "module='{$this->post->module}'";
  if ($newFile)
  {
    $data[] = "name='$newFile->name'";
    $data[] = "path='$newFile->path'";
    $data[] = "type='$newFile->type'";
    $data[] = "size='$newFile->size'";
  }
  if (count($data) <= 0)
  {
    throw new Exception(Message::make('error.notFound', 'data'));
  }

  // set output
  $output = Main::edit($this, (object)[
    'table' => 'files',
    'srl' => $srl,
    'data' => $data,
  ]);

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output data
  return Output::result($output);
}
catch (Exception $e)
{
  if ($this->model ?? false) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
