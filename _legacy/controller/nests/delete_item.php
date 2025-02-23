<?php
namespace Core;
use Controller\Main, Controller\files\UtilForFiles;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * delete nest
 *
 * @var Goose|Connect $this
 */

try
{
  // check and set srl
  if (($srl = (int)($this->params['srl'] ?? 0)) <= 0)
  {
    throw new Exception(Message::make('error.notFound', 'srl'));
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Main::checkAccessItem($this, (object)[
    'table' => 'nests',
    'srl' => $srl,
  ]);

  // remove articles
  $articles = $this->model->getItems((object)[
    'table' => 'articles',
    'field' => 'srl',
    'where' => 'nest_srl='.$srl,
  ]);
  if (count($articles->data ?? []) > 0)
  {
    foreach($articles->data as $k=>$v)
    {
      // remove thumbnail image
      UtilForFiles::removeThumbnailImage($this, $v->srl);
      // remove files
      UtilForFiles::removeAttachFiles($this, $v->srl, 'articles');
    }
    // remove articles
    $this->model->delete((object)[
      'table' => 'articles',
      'where' => 'nest_srl='.$srl,
    ]);
  }

  // remove categories
  $categoriesCount = $this->model->getCount((object)[
    'table' => 'categories',
    'where' => 'module="article" and target_srl='.$srl,
  ])->data;
  if ($categoriesCount > 0)
  {
    $this->model->delete((object)[
      'table' => 'categories',
      'where' => 'target_srl='.$srl,
    ]);
  }

  // remove nest
  $output = Main::delete($this, (object)[
    'table' => 'nests',
    'srl' => $srl,
  ]);

  // set output
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output data
  return Output::result($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
