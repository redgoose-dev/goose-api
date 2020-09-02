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
  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');

  // set values
  $subDir = ($this->post->sub_dir) ? $this->post->sub_dir : 'user';
  $month = date('Ym');

  // set path
  $path = 'data/upload/'.$subDir;
  $path_absolute = __API_PATH__.'/'.$path;
  $path_absolute_dest = $path_absolute.'/'.$month;

  // multipart upload
  if (isset($this->files['files']) && $this->files['files'])
  {
    $file = File::convertFilesValue($this->files['files']);
    $data = [];
    foreach ($file['name'] as $k=>$v)
    {
      // check file error
      if ($file['error'][$k])
      {
        $data[] = (object)[
          'name' => $file['name'][$k],
          'error' => true,
          'message' => Message::errorUploadFile($file['error'][$k]),
        ];
        continue;
      }

      // check file size
      if ((int)$file['size'][$k] > (int)$_ENV['API_FILE_LIMIT_SIZE'])
      {
        throw new Exception(Message::make('error.limitFileSize'));
      }

      // check filename
      $file['name'][$k] = File::checkFilename($file['name'][$k], false);
      if (!$file['name'][$k])
      {
        throw new Exception(Message::make('error.allowFileType'));
      }

      // check exist file
      $file['name'][$k] = File::checkExistFile($path_absolute_dest.'/', $file['name'][$k], null);

      // make month directory
      File::makeDirectory($path_absolute, 0707);

      // make month directory
      File::makeDirectory($path_absolute_dest, 0707);

      if (!($file['tmp_name'][$k] && is_dir($path_absolute_dest)))
      {
        throw new Exception('upload error', 204);
      }

      // copy file
      move_uploaded_file($file['tmp_name'][$k], $path_absolute_dest.'/'.$file['name'][$k]);

      // set output
      $data[] = (object)[
        'name' => $file['name'][$k],
        'path' => $path.'/'.$month.'/'.$file['name'][$k],
        'pathFull' => $_ENV['API_PATH_URL'].'/'.$path.'/'.$month.'/'.$file['name'][$k],
        'type' => $file['type'][$k],
        'size' => $file['size'][$k],
      ];
    }
  }
  // base64 upload
  else if ($this->post->base64)
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
    else
    {
      throw new Exception(Message::make('msg.onlyImage'));
    }
    $imgData = str_replace(' ', '+', $imgData);
    $imgSource = base64_decode($imgData);

    // check file size
    if ((int)strlen($imgSource) > (int)$_ENV['API_FILE_LIMIT_SIZE'])
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

  return Output::data($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::data($e->getMessage(), $e->getCode());
}
