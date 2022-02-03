<?php
namespace Core;
use Controller\Main, Controller\articles\UtilForArticles;
use Exception;

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
  $token = Main::checkAccessIndex($this, true);

  // set where
  $where = '';
  if ($app = ($this->get->app ?? null))
  {
    $where .= ' and app_srl='.$app;
  }
  if ($nest = ($this->get->nest ?? null))
  {
    $where .= ' and nest_srl='.$nest;
  }
  if ($category = ($this->get->category ?? null))
  {
    $where .= (strtolower($category) === 'null') ? ' and category_srl IS NULL' : ' and category_srl='.$category;
  }
  if ($q = ($this->get->q ?? null))
  {
    $where .= ' and (title LIKE \'%'.$q.'%\' or content LIKE \'%'.$q.'%\')';
  }
  $where .= UtilForArticles::getWhereType($this->get->visible_type ?? null);
  // `user_srl`값에 해당되는 값 가져오기
  if ($token->data->admin && isset($this->get->user))
  {
    $where .= ' and user_srl='.(int)$this->get->user;
  }
  else if (isset($token->data->srl) && !$token->data->admin)
  {
    $where .= ' and user_srl='.(int)$token->data->srl;
  }

  // set options
  $options = (object)array_merge(
    (array)$this->get,
    [
      'table' => 'articles',
      'where' => $where,
      'json_field' => ['json'],
      'object' => false,
      'debug' => __API_DEBUG__,
    ]
  );

  // set output
  $output = Main::index($this, $options);

  // set external items
  if ($output->data)
  {
    $ext_field = $this->get->ext_field ?? null;
    // get category name
    if (Util::checkKeyInExtField('category_name', $ext_field))
    {
      $output->data->index = UtilForArticles::extendCategoryNameInItems($this, $output->data->index);
    }
    // get nest name
    if (Util::checkKeyInExtField('nest_name', $ext_field))
    {
      $output->data->index = UtilForArticles::extendNestNameInItems($this, $output->data->index);
    }
    // get next page
    if (Util::checkKeyInExtField('next_page', $ext_field))
    {
      $nextPage = UtilForArticles::getNextPage($this, $where);
      if ($nextPage > 0) $output->data->nextPage = $nextPage;
    }
  }

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output
  return Output::result($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
