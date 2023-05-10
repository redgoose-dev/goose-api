<?php
namespace Core;
use Exception, Controller\Main;
use Controller\articles\UtilForArticles;

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
    $tableName = $this->model->getTableName('comments');
    $comments = $this->model->fetch('fetchAll', 'select article_srl from '.$tableName.' where (content LIKE \'%'.$q.'%\')');
    if (count($comments) > 0)
    {
      $comments = array_map(function($o) { return $o->article_srl; }, $comments);
      $comments = implode(',', array_unique($comments));
      $where .= ' or srl in ('.$comments.')';
    }
  }
  $where .= UtilForArticles::getWhereType($this->get->visible_type ?? null);
  // `user_srl`값에 해당되는 값 가져오기
  if (($token->data->admin ?? false) && ($this->get->user ?? false))
  {
    $where .= ' and user_srl='.(int)$this->get->user;
  }
  else if (($token->data->srl ?? false) && !($token->data->admin ?? false))
  {
    $where .= ' and user_srl='.(int)$token->data->srl;
  }
  if ($duration = ($this->get->duration ?? null))
  {
    $duration = explode(',', $duration);
    switch ($duration[0])
    {
      case 'new':
        $where .= ' and `'.$duration[1].'` > date_add(now(), interval -'.$duration[2].')';
        break;
      case 'old':
        $where .= ' and `'.$duration[1].'` < date_sub(now(), interval '.$duration[2].')';
        break;
    }
  }

  // set order
  if ($random = ($this->get->random ?? null))
  {
    $order = 'rand('.$random.')';
  }

  // set options
  $options = (object)array_merge(
    (array)$this->get,
    [
      'table' => 'articles',
      'where' => $where,
      'json_field' => [ 'json' ],
      'object' => false,
      'order' => $order ?? null,
      'debug' => __API_DEBUG__,
    ]
  );

  // set output
  $output = Main::index($this, $options);

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
  if ($this->model ?? false) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
