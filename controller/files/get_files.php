<?php
namespace Core;
use Exception, Controller\Main;
use Controller\files\UtilForFiles;

if (!defined('__API_GOOSE__')) exit();

/**
 * get files in directory
 *
 * @var Goose|Connect $this
 */

try
{
  // check upload directories
  Util::checkDirectories();

  // check and set dir
  if (!($dir = $this->params['dir']))
  {
    throw new Exception(Message::make('error.notFound', 'dir'));
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Main::checkAccessIndex($this, true);

  // set path
  $path = 'data/upload/'.$dir;

  // create assets map file
  $files = UtilForFiles::createAssetsMapFile($dir);
  if (!$files) $files = UtilForFiles::getAssetsMapFiles($dir);
  if (!$files) throw new Exception(Message::make('error.notFound', 'file'), 404);

  // set tree
  $tree = [];
  foreach ($files as $key => $file)
  {
    $tree[] = (object)array_merge((array)$file, [ 'path' => $key ]);
  }

  // sort tree
  if (count($tree) > 0)
  {
    switch ($this->get->order ?? '')
    {
      case 'name':
        usort($tree, function($a, $b) {
          return strcmp($a->name, $b->name);
        });
        usort($tree, function($a,$b) {
          $diff = (($this->get->sort ?? '') === 'desc') ? $a->name < $b->name : $a->name > $b->name;
          return $diff ? 1 : -1;
        });
        break;
      case 'date':
      default:
        usort($tree, function($a, $b) {
          return strcmp($a->date, $b->date);
        });
        usort($tree, function($a,$b) {
          $diff = (($this->get->sort ?? '') === 'desc') ? $a->date < $b->date : $a->date > $b->date;
          return $diff ? 1 : -1;
        });
        break;
    }
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
  return Output::result($output);
}
catch (Exception $e)
{
  if ($this->model ?? false) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
