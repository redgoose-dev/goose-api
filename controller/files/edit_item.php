<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit file
 *
 * @var Goose|Connect $this
 */

try
{
  // check and set srl
  $srl = (int)$this->params['srl'];
  if (!($srl && $srl > 0))
  {
    throw new Exception(Message::make('error.notFound', 'srl'));
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessItem($this, (object)[
    'table' => 'files',
    'srl' => $srl,
  ]);

  // check target_srl
  if (isset($this->post->check) && $this->post->check)
  {
    if (isset($this->post->target_srl) && isset($this->post->module))
    {
      Controller\files\UtilForFiles::checkTargetData(
        $this,
        (int)$this->post->target_srl,
        $this->post->module,
        $token
      );
    }
    else
    {
      throw new Exception(Message::make('error.noItem', 'target_srl and module'));
    }
  }

  // remove and upload file
  $file = File::convertFilesValue($this->files['files']);
  if (isset($file['name'][0]))
  {
    /**
     * upload file
     */
    // set values
    $month = date('Ym');
    $subDir = ($this->post->sub_dir) ? $this->post->sub_dir : $_ENV['API_DEFAULT_UPLOAD_DIR_NAME'];
    // set path
    $path = 'data/upload/'.$subDir;
    $path_absolute = __API_PATH__.'/'.$path;
    $path_absolute_dest = $path_absolute.'/'.$month;
    // make sub directory
    File::makeDirectory($path_absolute, 0707);
    // make month directory
    File::makeDirectory($path_absolute_dest, 0777);
    // check file
    if ($file['error'][0])
    {
      throw new Exception($file['error'][0]);
    }
    // check file size
    if ((int)$file['size'][0] > (int)$_ENV['API_FILE_LIMIT_SIZE'])
    {
      throw new Exception(Message::make('error.limitFileSize'));
    }
    // check filename
    $file['name'][0] = File::checkFilename($file['name'][0], false);
    if (!$file['name'][0])
    {
      throw new Exception(Message::make('error.allowFileType'));
    }
    // check exist filename
    $file['name'][0] = File::checkExistFile($path_absolute_dest.'/', $file['name'][0], null);
    // check path and tmp_name
    if (!($file['tmp_name'][0] && is_dir($path_absolute_dest)))
    {
      throw new Exception('upload error');
    }
    // copy file to target
    move_uploaded_file($file['tmp_name'][0], $path_absolute_dest.'/'.$file['name'][0]);
    // set new file
    $newFile = (object)[
      'name' => $file['name'][0],
      'path' => $path.'/'.$month.'/'.$file['name'][0],
      'type' => $file['type'][0],
      'size' => $file['size'][0],
    ];

    /**
     * remove file
     */
    // get item
    $file = $this->model->getItem((object)[
      'table' => 'files',
      'field' => 'path',
      'where' => 'srl='.$srl,
    ]);
    // check exist file
    if (isset($file->data->path) && $file->data->path && file_exists(__API_PATH__.'/'.$file->data->path))
    {
      @unlink(__API_PATH__.'/'.$file->data->path);
    }
  }
  else
  {
    $newFile = null;
  }

  // update data
  $data = [];
  if ($this->post->target_srl) $data[] = "target_srl='{$this->post->target_srl}'";
  if ($this->post->module) $data[] = "module='{$this->post->module}'";
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
  $output = Controller\Main::edit($this, (object)[
    'table' => 'files',
    'srl' => $srl,
    'data' => $data,
  ]);

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output data
  return Output::data($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::data($e->getMessage(), $e->getCode());
}
