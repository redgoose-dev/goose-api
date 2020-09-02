<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * get articles
 *
 * @var Goose|Connect $this
 */

try
{
  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessIndex($this, true);

  // set where
  $where = '';
  if ($app = $this->get->app)
  {
    $where .= ' and app_srl='.$app;
  }
  if ($nest = $this->get->nest)
  {
    $where .= ' and nest_srl='.$nest;
  }
  if ($category = $this->get->category)
  {
    $where .= (strtolower($category) === 'null') ? ' and category_srl IS NULL' : ' and category_srl='.$category;
  }
  if ($q = $this->get->q)
  {
    $where .= ' and (title LIKE \'%'.$q.'%\' or content LIKE \'%'.$q.'%\')';
  }
  $where .= Controller\articles\UtilForArticles::getWhereType($this->get->visible_type);
  // `user_srl`값에 해당되는 값 가져오기
  if ($token->data->admin && isset($this->get->user))
  {
    $where .= ' and user_srl='.(int)$this->get->user;
  }
  else if (isset($token->data->user_srl) && !$token->data->admin)
  {
    $where .= ' and user_srl='.(int)$token->data->user_srl;
  }

  // set output
  $output = Controller\Main::index($this, (object)array_merge((array)$this->get, (array)[
    'table' => 'articles',
    'where' => $where,
    'json_field' => ['json'],
    'object' => false,
    'debug' => __API_DEBUG__,
  ]));

  // get category name
  if ($output->data && Util::checkKeyInExtField('category_name', $this->get->ext_field))
  {
    $output->data->index = Controller\articles\UtilForArticles::extendCategoryNameInItems(
      $this,
      $output->data->index
    );
  }
  // get nest name
  if ($output->data && Util::checkKeyInExtField('nest_name', $this->get->ext_field))
  {
    $output->data->index = Controller\articles\UtilForArticles::extendNestNameInItems(
      $this,
      $output->data->index
    );
  }
  // get next page
  if ($output->data && Util::checkKeyInExtField('next_page', $this->get->ext_field))
  {
    $nextPage = Controller\articles\UtilForArticles::getNextPage($this, $where);
    if ($nextPage) $output->data->nextPage = $nextPage;
  }

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output
  return Output::data($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::data($e->getMessage(), $e->getCode());
}
