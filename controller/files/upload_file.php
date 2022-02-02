<?php
namespace Core;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * upload file
 *
 * @var Goose|Connect $this
 */

try
{
  // check upload directories
  Util::checkDirectories();

  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');

  // set values
  $subDir = $this->post->sub_dir ?? 'user';
  $month = date('Ym');

  // set path
  $path = 'data/upload/'.$subDir;
  $path_absolute = __API_PATH__.'/'.$path;
  $path_absolute_dest = $path_absolute.'/'.$month;

  // multipart upload
  if ($this->files['file'] ?? false)
  {
    $file = File::convertFilesValue($this->files['file']);

    // check file error
    if ($file['error'])
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
    // check exist file
    $file['name'] = File::checkExistFile($path_absolute_dest.'/', $file['name'], null);

    // make month directory
    File::makeDirectory($path_absolute, 0707);

    // make month directory
    File::makeDirectory($path_absolute_dest, 0707);

    // copy file to target
    if ($file['tmp_name'] && is_dir($path_absolute_dest))
    {
      move_uploaded_file($file['tmp_name'], $path_absolute_dest.'/'.$file['name']);
    }
    else
    {
      throw new Exception(Message::make('error.upload'));
    }

    // set output
    $data = (object)[
      'name' => $file['name'],
      'path' => $path.'/'.$month.'/'.$file['name'],
      'pathFull' => $_ENV['API_PATH_URL'].'/'.$path.'/'.$month.'/'.$file['name'],
      'type' => $file['type'],
      'size' => $file['size'],
    ];
  }
  // base64 upload
  else if ($this->post->base64 ?? false)
  {
    // make image
    $imgData = $this->post->base64;
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
    else if (preg_match("/^data:image\/webp/", $imgData))
    {
      $filename = uniqid().'.webp';
      $imgData = str_replace('data:image/webp;base64,', '', $imgData);
      $imgType = 'image/webp';
    }
    else
    {
      throw new Exception(Message::make('msg.onlyImage'));
    }
    $imgData = str_replace(' ', '+', $imgData);
    $imgSource = base64_decode($imgData);

    // check file size
    if (strlen($imgSource) > (int)$_ENV['API_FILE_LIMIT_SIZE'])
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
      'pathFull' => $_ENV['API_PATH_URL'].'/'.$path.'/'.$month.'/'.$filename,
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

  return Output::result($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
