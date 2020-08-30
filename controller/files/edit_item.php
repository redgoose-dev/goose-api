<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit file
 *
 * @var Goose $this
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
  $token = Controller\Main::checkAccessItem((object)[
    'model' => $this->model,
    'table' => 'files',
    'srl' => $srl,
  ]);

  // check target data
  if (isset($_POST['check']) && isset($_POST['target_srl']) && isset($_POST['module']))
  {
    Controller\files\UtilForFiles::checkTargetData(
      $this->model,
      (int)$_POST['target_srl'],
      $_POST['module'],
      $token
    );
  }

  // remove and upload file
  if ($_FILES['files'] && $_FILES['files']['name'])
  {
    /**
     * upload file
     */
    // set values
    $file = $_FILES['files'];
    $month = date('Ym');
    $subDir = ($_POST['sub_dir']) ? $_POST['sub_dir'] : $_ENV['API_DEFAULT_UPLOAD_DIR_NAME'];
    // set path
    $path = 'data/upload/'.$subDir;
    $path_absolute = __API_PATH__.'/'.$path;
    $path_absolute_dest = $path_absolute.'/'.$month;
    // make sub directory
    File::makeDirectory($path_absolute, 0707);
    // make month directory
    File::makeDirectory($path_absolute_dest, 0777);
    // check file
    if ($file['error'])
    {
      throw new Exception($file['error']);
    }
    // check file size
    if ((int)$file['size'] > (int)$_ENV['API_FILE_LIMIT_SIZE'])
    {
      throw new Exception(Message::make('error.limitFileSize'));
    }
    // check filename
    $file['name'] = File::checkFilename($file['name'], false);
    if (!$file['name'])
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
    ]);
    // check exist file
    if (
      isset($file->data->path) && $file->data->path &&
      file_exists(__API_PATH__.'/'.$file->data->path)
    )
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
  if ($_POST['target_srl']) $data[] = "target_srl='$_POST[target_srl]'";
  if ($_POST['module']) $data[] = "module='$_POST[module]'";
  if ($newFile)
  {
    $data[] = "name='$newFile->name'";
    $data[] = "path='$newFile->path'";
    $data[] = "type='$newFile->type'";
    $data[] = "size='$newFile->size'";
  }

  // set output
  $output = Controller\Main::edit((object)[
    'model' => $this->model,
    'table' => 'files',
    'srl' => $srl,
    'data' => $data,
  ]);

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output data
  Output::data($output);
}
catch (Exception $e)
{
  $this->model->disconnect();
  Error::data($e->getMessage(), $e->getCode());
}
