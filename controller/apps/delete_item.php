<?php
namespace Core;
use Exception, Controller;

if (!defined('__GOOSE__')) exit();

/**
 * delete app
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
  $token = Controller\Main::checkAccessItem((object)[
    'model' => $this->model,
    'table' => 'apps',
    'srl' => $srl,
  ]);

  // get articles list
  $articles = $this->model->getItems((object)[
    'table' => 'articles',
    'field' => 'srl',
    'where' => 'app_srl='.$srl,
  ]);
  if ($articles->data && count($articles->data))
  {
    foreach($articles->data as $k=>$v)
    {
      // remove thumbnail image
      Controller\files\UtilForFiles::removeThumbnailImage($this->model, $v->srl);
      // remove files
      Controller\files\UtilForFiles::removeAttachFiles($this->model, $v->srl, 'articles');
    }
    // remove articles
    $this->model->delete((object)[
      'table' => 'articles',
      'where' => 'app_srl='.$srl,
    ]);
  }

  // get nests list
  $nests = $this->model->getItems((object)[
    'table' => 'nests',
    'field' => 'srl',
    'where' => 'app_srl='.$srl,
  ]);
  if ($nests->data && count($nests->data))
  {
    // remove categories
    foreach($nests->data as $k=>$v)
    {
      $this->model->delete((object)[
        'table' => 'categories',
        'where' => 'nest_srl='.(int)$v->srl,
      ]);
    }
    // remove nests
    $this->model->delete((object)[
      'table' => 'nests',
      'where' => 'app_srl='.$srl
    ]);
  }

  // remove app
  $output = Controller\Main::delete((object)[
    'model' => $this->model,
    'table' => 'apps',
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
