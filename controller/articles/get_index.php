<?php
namespace Core;
use Exception, Controller;

if (!defined('__GOOSE__')) exit();

/**
 * get articles
 *
 * @var Goose $this
 */

try
{
  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessIndex($this->model, true);

  // set where
  $where = '';
  if ($app = $_GET['app'])
  {
    $where .= ' and app_srl='.$app;
  }
  if ($nest = $_GET['nest'])
  {
    $where .= ' and nest_srl='.$nest;
  }
  if ($category = $_GET['category'])
  {
    $where .= ($category === 'null') ? ' and category_srl IS NULL' : ' and category_srl='.$category;
  }
  if ($q = $_GET['q'])
  {
    $where .= ' and (title LIKE \'%'.$q.'%\' or content LIKE \'%'.$q.'%\')';
  }
  $where .= Controller\articles\UtilForArticles::getWhereType();
  // `user_srl`값에 해당되는 값 가져오기
  if (isset($token->data->user_srl) && !$token->data->admin)
  {
    $where .= ' and user_srl='.(int)$token->data->user_srl;
  }

  // set output
  $output = Controller\Main::index((object)[
    'model' => $this->model,
    'table' => 'articles',
    'where' => $where,
    'json_field' => ['json']
  ]);

  // get category name
  if ($output->data && Util::checkKeyInExtField('category_name'))
  {
    $output->data->index = Controller\articles\UtilForArticles::extendCategoryNameInItems(
      $this->model,
      $output->data->index
    );
  }

  // get nest name
  if ($output->data && Util::checkKeyInExtField('nest_name'))
  {
    $output->data->index = Controller\articles\UtilForArticles::extendNestNameInItems(
      $this->model,
      $output->data->index
    );
  }

  // get next page
  if ($output->data && Util::checkKeyInExtField('next_page'))
  {
    $nextPage = Controller\articles\UtilForArticles::getNextPage(
      $this->model,
      $where
    );
    if ($nextPage) $output->data->nextPage = $nextPage;
  }

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output
  Output::data($output);
}
catch (Exception $e)
{
  $this->model->disconnect();
  Error::data($e->getMessage(), $e->getCode());
}
