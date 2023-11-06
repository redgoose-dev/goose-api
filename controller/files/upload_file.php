<?php
namespace Core;
use Exception;
use Controller\files\UtilForFiles;

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
  $dir = $this->post->dir ?? 'user';
  $month = date('Ym');
  $localPath = null;

  // set path
  $pathAbsoluteDest = UtilForFiles::$uploadFull.$dir.'/'.$month;

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
    $file['name'] = File::checkExistFile($pathAbsoluteDest.'/', $file['name'], null);

    // make month directory
    File::makeDirectory($pathAbsoluteDest, 0707, true);

    // copy file to target
    if ($file['tmp_name'] && is_dir($pathAbsoluteDest))
    {
      move_uploaded_file($file['tmp_name'], $pathAbsoluteDest.'/'.$file['name']);
    }
    else
    {
      throw new Exception(Message::make('error.upload'));
    }

    // set data
    $localPath = $month.'/'.$file['name'];
    $data = (object)[
      'name' => $file['name'],
      'size' => $file['size'],
      'date' => filemtime($pathAbsoluteDest.'/'.$file['name']),
      'type' => $file['type'],
    ];
    if (str_starts_with($data->type, 'image'))
    {
      list( $width, $height ) = getimagesize($pathAbsoluteDest.'/'.$file['name']);
      $data->image = (object)[ 'width' => $width, 'height' => $height ];
    }
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
    $filename = File::checkExistFile($pathAbsoluteDest.'/', $filename, null);

    // make month directory
    File::makeDirectory($pathAbsoluteDest, 0707, true);

    // check dest dir
    if (!is_dir($pathAbsoluteDest))
    {
      throw new Exception(Message::make('error.upload'));
    }

    // upload file
    $uploadedFileSize = file_put_contents($pathAbsoluteDest.'/'.$filename, $imgSource);

    // set data
    $localPath = $month.'/'.$filename;
    $data = (object)[
      'name' => $filename,
      'size' => $uploadedFileSize,
      'date' => filemtime($pathAbsoluteDest.'/'.$filename),
      'type' => $imgType,
    ];
    list( $width, $height ) = getimagesize($pathAbsoluteDest.'/'.$filename);
    $data->image = (object)[ 'width' => $width, 'height' => $height ];
  }
  else
  {
    throw new Exception(Message::make('error.noValueTo', 'value', 'upload'));
  }

  // get map.json file
  $json = UtilForFiles::getAssetsMapFiles($dir);
  if (!$json) $json = UtilForFiles::createAssetsMapFile($dir);

  // update map
  if ($localPath)
  {
    $json->{$localPath} = $data;
    UtilForFiles::writeAssetsMapFile($json, $dir);
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
  if ($this->model ?? false) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
