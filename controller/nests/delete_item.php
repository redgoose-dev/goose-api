<?php
namespace Core;
use Controller\files\UtilForFiles;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * delete nest
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
  $token = Controller::checkAccessItem((object)[
    'model' => $this->model,
    'table' => 'nests',
    'srl' => $srl,
  ]);

  // remove articles
  $articles = $this->model->getItems((object)[
    'table' => 'articles',
    'field' => 'srl',
    'where' => 'nest_srl='.$srl,
  ]);
  if ($articles->data && count($articles->data))
  {
    foreach($articles->data as $k=>$v)
    {
      // remove thumbnail image
      UtilForFiles::removeThumbnailImage($this->model, $v->srl);
      // remove files
      UtilForFiles::removeAttachFiles($this->model, $v->srl);
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
    'where' => 'nest_srl='.$srl,
  ]);
  if ($categoriesCount->data > 0)
  {
    $this->model->delete((object)[
      'table' => 'categories',
      'where' => 'nest_srl='.$srl,
    ]);
  }

  // remove nest
  $output = Controller::delete((object)[
    'model' => $this->model,
    'table' => 'nests',
    'srl' => $srl,
  ]);

  // set output
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
