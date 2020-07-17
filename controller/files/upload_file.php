<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * upload file
 *
 * @var Goose $this
 */

try
{
  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');

  // set values
  $subDir = ($_POST['sub_dir']) ? $_POST['sub_dir'] : 'user';
  $month = date('Ym');

  // set path
  $path = 'data/upload/'.$subDir;
  $path_absolute = __PATH__.'/'.$path;
  $path_absolute_dest = $path_absolute.'/'.$month;

  // multipart upload
  if ($_FILES['file'])
  {
    $file = $_FILES['file'];

    // check file error
    if ($file['error']) throw new Exception('Error file');

    // check file size
    if ((int)$file['size'] > (int)$_ENV['FILE_LIMIT_SIZE'])
    {
      throw new Exception(Message::make('error.limitFileSize'));
    }

    // check filename
    $file['name'] = File::checkFilename($file['name'], false);
    if (!$file['name'])
    {
      throw new Exception(Message::make('error.allowFileType'));
    }

    // check exist file
    $file['name'] = File::checkExistFile($path_absolute_dest.'/', $file['name'], null);

    // make month directory
    File::makeDirectory($path_absolute, 0707);

    // make month directory
    File::makeDirectory($path_absolute_dest, 0707);

    if (!($file['tmp_name'] && is_dir($path_absolute_dest)))
    {
      throw new Exception('upload error', 204);
    }

    // copy file
    move_uploaded_file($file['tmp_name'], $path_absolute_dest.'/'.$file['name']);

    // set output
    $data = (object)[
      'name' => $file['name'],
      'path' => $path.'/'.$month.'/'.$file['name'],
      'pathFull' => $_ENV['PATH_URL'].'/'.$path.'/'.$month.'/'.$file['name'],
      'type' => $file['type'],
      'size' => $file['size'],
    ];
  }
  // base64 upload
  else if ($_POST['base64'])
  {
    // make image
    $imgData = $_POST['base64'];
    if (preg_match("/^data:image\/jpeg/", $imgData))
    {
      $filename = uniqid().'.jpg';
      $imgData = str_replace('data:image/jpeg;base64,', '', $imgData);
      $imgType = 'image/jpeg';
    }
    else if (preg_match("/^data:image\/png/", $imgData))
    {
      $filename = uniqid().'.png';
      $imgData = str_replace('data:image/png;base64,', '', $imgData);
      $imgType = 'image/png';
    }
    else
    {
      throw new Exception(Message::make('msg.onlyImage'));
    }
    $imgData = str_replace(' ', '+', $imgData);
    $imgSource = base64_decode($imgData);

    // check file size
    if ((int)strlen($imgSource) > (int)$_ENV['FILE_LIMIT_SIZE'])
    {
      throw new Exception(Message::make('error.limitFileSize'));
    }

    // check filename
    $filename = File::checkFilename($filename, false);
    if (!$filename)
    {
      throw new Exception(Message::make('error.allowFileType'));
    }

    // check exist file
    $filename = File::checkExistFile($path_absolute_dest.'/', $filename, null);

    // make month directory
    File::makeDirectory($path_absolute, 0707);

    // make month directory
    File::makeDirectory($path_absolute_dest, 0707);

    // check dest dir
    if (!is_dir($path_absolute_dest))
    {
      throw new Exception(Message::make('error.upload'));
    }

    // upload file
    $uploadedFileSize = file_put_contents($path_absolute_dest.'/'.$filename, $imgSource);

    // set data
    $data = (object)[
      'name' => $filename,
      'path' => $path.'/'.$month.'/'.$filename,
      'pathFull' => $_ENV['PATH_URL'].'/'.$path.'/'.$month.'/'.$filename,
      'type' => $imgType,
      'size' => $uploadedFileSize,
    ];
  }
  else
  {
    throw new Exception(Message::make('error.noValueTo', 'value', 'upload'));
  }

  // set output
  $output = (object)[];
  $output->code = 200;
  $output->data = $data;

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  Output::data($output);
}
catch (Exception $e)
{
  $this->model->disconnect();
  Error::data($e->getMessage(), $e->getCode());
}
