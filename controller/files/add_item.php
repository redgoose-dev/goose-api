<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * add file
 *
 * @var Goose $this
 */

try
{
  // set and check file
  if ($_FILES['file']) $_FILES['files'] = $_FILES['file'];
  if (!($_FILES['files'] && $_FILES['files']['name']))
  {
    throw new Exception(Message::make('error.notFound', 'files'));
  }

  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');

  // check target_srl
  if (isset($_POST['check']) && isset($_POST['target_srl']) && isset($_POST['module']))
  {
    Controller\files\UtilForFiles::checkTargetData(
      $this->model,
      (int)$_POST['target_srl'],
      $_POST['module'],
      $token
    );
  }

  // string to array files
  if (!is_array($_FILES['files']['name']))
  {
    $file = [];
    $file['name'] = [ $_FILES['files']['name'] ];
    $file['type'] = [ $_FILES['files']['type'] ];
    $file['tmp_name'] = [ $_FILES['files']['tmp_name'] ];
    $file['size'] = [ $_FILES['files']['size'] ];
    $file['error'] = [ $_FILES['files']['error'] ];
  }
  else
  {
    $file = $_FILES['files'];
  }

  // set variable
  $result = [];
  $month = date('Ym');
  $subDir = ($_POST['sub_dir']) ? $_POST['sub_dir'] : $_ENV['API_DEFAULT_UPLOAD_DIR_NAME'];

  // set path
  $path = 'data/upload/'.$subDir;
  $path_absolute = __API_PATH__.'/'.$path;
  $path_absolute_dest = $path_absolute.'/'.$month;

  // make sub directory
  File::makeDirectory($path_absolute, 0707);

  // make month directory
  File::makeDirectory($path_absolute_dest, 0707);

  // play upload
  foreach ($file['name'] as $k=>$v)
  {
    if ($file['error'][$k])
    {
      $result[] = (object)[
        'status' => 'error',
        'message' => Message::errorUploadFile($file['error'][$k]),
      ];
      continue;
    }

    // check file size
    if ((int)$file['size'][$k] > (int)$_ENV['API_FILE_LIMIT_SIZE'])
    {
      $result[] = (object)[
        'status' => 'error',
        'message' => Message::make('error.limitFileSize'),
      ];
      continue;
    }

    // check filename
    $file['name'][$k] = File::checkFilename($file['name'][$k], false);
    if (!$file['name'][$k])
    {
      $result[] = (object)[
        'status' => 'error',
        'message' => Message::make('error.allowFileType'),
      ];
      continue;
    }

    // check exist file
    $file['name'][$k] = File::checkExistFile($path_absolute_dest.'/', $file['name'][$k], null);

    // copy file to target
    if ($file['tmp_name'][$k] && is_dir($path_absolute_dest))
    {
      move_uploaded_file($file['tmp_name'][$k], $path_absolute_dest.'/'.$file['name'][$k]);
    }
    else
    {
      $result[] = (object)[
        'status' => 'error',
        'message' => Message::make('error.upload'),
      ];
      continue;
    }

    try
    {
      // insert data
      $this->model->add((object)[
        'table' => 'files',
        'data' => (object)[
          'srl' => null,
          'target_srl' => $_POST['target_srl'] ? (int)$_POST['target_srl'] : null,
          'user_srl' => (int)$token->data->user_srl,
          'name' => $file['name'][$k],
          'path' => $path.'/'.$month.'/'.$file['name'][$k],
          'type' => $file['type'][$k],
          'size' => (int)$file['size'][$k],
          'regdate' => date('Y-m-d H:i:s'),
          'module' => $_POST['module'] ? $_POST['module'] : null,
        ],
      ]);

      // set result
      $result[] = (object)[
        'status' => 'success',
        'path' => $path.'/'.$month.'/'.$file['name'][$k],
        'name' => $file['name'][$k],
        'size' => $file['size'][$k],
        'type' => $file['type'][$k],
        'srl' => $this->model->getLastIndex(),
      ];
    }
    catch (Exception $e)
    {
      // remove file
      if (file_exists($path_absolute_dest.'/'.$file['name'][$k]))
      {
        @unlink($path_absolute_dest.'/'.$file['name'][$k]);
      }
      $result[] = (object)[
        'status' => 'error',
        'message' => 'DB ERROR: '.$e->getMessage(),
      ];
      continue;
    }
  }

  // set output
  $output = (object)[];
  $output->code = 200;
  $output->data = $result;

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
