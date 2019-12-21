<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * delete app
 *
 * @var Goose $this
 */

try
{
  $tableName = 'apps';
  $srl = (int)$this->params['srl'];

  // check srl
  if (!($srl && $srl > 0))
  {
    throw new Exception('Not found srl', 204);
  }

  // set model
  $model = new Model();
  $model->connect();

  // check access
  $token = Controller::checkAccessItem((object)[
    'model' => $model,
    'table' => $tableName,
    'srl' => $srl,
  ]);

  // get articles list
  $articles = $model->getItems((object)[
    'table' => 'articles',
    'field' => 'srl',
    'where' => 'app_srl='.$srl,
  ]);
  if ($articles->data && count($articles->data))
  {
    foreach($articles->data as $k=>$v)
    {
      // remove thumbnail image
      Controller::removeThumbnailImage($model, $v->srl);

      // remove files
      Controller::removeAttachFiles($model, $v->srl);
    }
    // remove articles
    $model->delete((object)[
      'table' => 'articles',
      'where' => 'app_srl='.$srl
    ]);
  }

  // get nests list
  $nests = $model->getItems((object)[
    'table' => 'nests',
    'field' => 'srl',
    'where' => 'app_srl='.$srl,
  ]);
  if ($nests->data && count($nests->data))
  {
    // remove categories
    foreach($nests->data as $k=>$v)
    {
      $model->delete((object)[
        'table' => 'categories',
        'where' => 'nest_srl='.(int)$v->srl,
      ]);
    }

    // remove nests
    $model->delete((object)[
      'table' => 'nests',
      'where' => 'app_srl='.$srl
    ]);
  }

  // remove app
  $output = Controller::delete((object)[
    'goose' => $this,
    'model' => $model,
    'table' => $tableName,
    'srl' => $srl,
  ]);

  // set output
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $model->disconnect();

  // output data
  Output::data($output);
}
catch (Exception $e)
{
  if (isset($model)) $model->disconnect();
  Error::data($e->getMessage(), $e->getCode());
}
