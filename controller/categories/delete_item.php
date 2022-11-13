<?php
namespace Core;
use Exception, Controller\Main;
use Controller\categories\UtilForCategories;

if (!defined('__API_GOOSE__')) exit();

/**
 * delete category
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
    'table' => 'categories',
    'srl' => $srl,
  ]);

  // get item
  $category = $this->model->getItem((object)[
    'table' => 'categories',
    'where' => 'srl='.$srl,
  ])->data;

  // update article items
  $tableName = match ($category->module)
  {
    UtilForCategories::$module['article'] => 'articles',
    UtilForCategories::$module['json'] => 'json',
  };
  if ($tableName ?? false)
  {
    $this->model->edit((object)[
      'table' => $tableName,
      'data' => [ 'category_srl=NULL' ],
      'where' => 'category_srl='.$srl,
      'continue' => true,
      'debug' => __API_DEBUG__,
    ]);
  }

  // remove data
  $output = Main::delete($this, (object)[
    'table' => 'categories',
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
