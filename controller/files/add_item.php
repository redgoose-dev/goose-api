<?php
namespace Core;
use Exception;
use Controller\files\UtilForFiles;

if (!defined('__API_GOOSE__')) exit();

/**
 * add file
 *
 * @var Goose|Connect $this
 */

try
{
  // check upload directories
  Util::checkDirectories();

  // check post values
  Util::checkExistValue($this->post, [ 'module', 'target_srl' ]);

  // check file
  if (!($this->files['file']['name'] ?? false))
  {
    throw new Exception(Message::make('error.notFound', 'file'));
  }

  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');

  // `target_srl`값이 실제로 존재하는지 검사한다.
  UtilForFiles::checkTargetData(
    $this,
    (int)$this->post->target_srl,
    $this->post->module,
    $token
  );

  // set variable
  $month = date('Ym');
  $subDir = $this->post->sub_dir ?? $_ENV['API_DEFAULT_UPLOAD_DIR_NAME'];
  $json = (object)[];

  // set path
  $path = 'data/upload/'.$subDir;
  $path_absolute = __API_PATH__.'/'.$path;
  $path_absolute_dest = $path_absolute.'/'.$month;

  // make month directory
  File::makeDirectory($path_absolute_dest, 0707, true);

  // $_FILES to array
  $file = File::convertFilesValue($this->files['file'] ?? []);

  // check file error
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
  // check exist file
  $file['name'] = File::checkExistFile($path_absolute_dest.'/', $file['name'], null);

  // copy file to target
  if ($file['tmp_name'] && is_dir($path_absolute_dest))
  {
    $pathDest = $path_absolute_dest.'/'.$file['name'];
    move_uploaded_file($file['tmp_name'], $pathDest);
    if (str_starts_with($file['type'], 'image'))
    {
      $size = getimagesize($pathDest);
      $json->width = $size[0];
      $json->height = $size[1];
    }
  }
  else
  {
    throw new Exception(Message::make('error.upload'));
  }

  try
  {
    // insert data
    $this->model->add((object)[
      'table' => 'files',
      'data' => (object)[
        'srl' => null,
        'target_srl' => (int)$this->post->target_srl,
        'user_srl' => (int)$token->data->srl,
        'name' => $file['name'],
        'path' => $path.'/'.$month.'/'.$file['name'],
        'type' => $file['type'],
        'size' => (int)$file['size'],
        'module' => $this->post->module ?? null,
        'json' => json_encode($json),
        'regdate' => date('Y-m-d H:i:s'),
      ],
    ]);
    // set result
    $result = (object)[
      'status' => 'success',
      'path' => $path.'/'.$month.'/'.$file['name'],
      'name' => $file['name'],
      'size' => $file['size'],
      'type' => $file['type'],
      'srl' => $this->model->getLastIndex(),
    ];
  }
  catch (Exception $e)
  {
    // remove file
    if (file_exists($path_absolute_dest.'/'.$file['name']))
    {
      @unlink($path_absolute_dest.'/'.$file['name']);
    }
    throw new Exception(Message::make('error.failedAddData'));
  }

  // set output
  $output = (object)[];
  $output->code = 200;
  $output->data = $result ?? null;

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
