<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * get files in directory
 *
 * @var Goose $this
 */

try
{
  // check and set dir
  $dir = $this->params['dir'];
  if (!$dir)
  {
    throw new Exception(Message::make('error.notFound', 'dir'));
  }

  // check access
  $token = Controller\Main::checkAccessIndex(null, true);

  // set path
  $path = 'data/upload/'.$dir;
  $path_absolute = __API_PATH__.'/'.$path;

  // get directories
  $directories = File::getDirectories($path_absolute);

  // set tree
  $tree = [];
  foreach ($directories as $dir)
  {
    $files = File::getFiles($path_absolute.'/'.$dir);
    if (!count($files)) continue;
    foreach ($files as $file)
    {
      $filePath = $path_absolute.'/'.$dir.'/'.$file;
      $tree[] = (object)[
        'name' => $file,
        'path' => $path.'/'.$dir.'/'.$file,
        'size' => filesize($filePath),
        'date' => filemtime($filePath),
        'type' => mime_content_type($filePath),
      ];
    }
  }
  if (count($tree) > 0)
  {
    switch ($_GET['order'])
    {
      case 'name':
        usort($tree, function($a, $b) {
          return strcmp($a->name, $b->name);
        });
        usort($tree,function($a,$b) {
          return ($_GET['sort'] && $_GET['sort'] === 'desc') ? $a->name < $b->name : $a->name > $b->name;
        });
        break;
      case 'date':
      default:
        usort($tree, function($a, $b) {
          return strcmp($a->date, $b->date);
        });
        usort($tree,function($a,$b) {
          return ($_GET['sort'] && $_GET['sort'] === 'desc') ? $a->date < $b->date : $a->date > $b->date;
        });
        break;
    }
  }
  else
  {
    throw new Exception('Not found files', 404);
  }

  // set output
  $output = (object)[];
  $output->code = 200;
  $output->data = (object)[
    'total' => count($tree),
    'index' => $tree,
  ];

  // set token
  if ($token) $output->_token = $token->jwt;

  // output
  Output::data($output);
}
catch (Exception $e)
{
  Error::data($e->getMessage(), $e->getCode());
}
