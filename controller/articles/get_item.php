<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * get article
 *
 * @var Goose|Connect $this
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
  $token = Controller\Main::checkAccessItem($this, (object)[
    'table' => 'articles',
    'srl' => $srl,
    'useStrict' => true,
  ]);

  // set where
  $where = ($app = $this->get->app) ? ' and app_srl='.$app : '';
  if ($nest = $this->get->nest)
  {
    $where .= ' and nest_srl='.$nest;
  }
  $where .= Controller\articles\UtilForArticles::getWhereType($this->get->visible_type);
  // `user_srl`값에 해당되는 값 가져오기
  if (isset($token->data->user_srl) && !$token->data->admin)
  {
    $where .= ' and user_srl='.(int)$token->data->user_srl;
  }

  // set output
  $output = Controller\Main::item($this, (object)[
    'table' => 'articles',
    'srl' => $srl,
    'where' => $where,
    'field' => $this->get->field,
    'json_field' => ['json'],
  ]);

  // get category name
  if (isset($output->data->category_srl) && Util::checkKeyInExtField('category_name', $this->get->ext_field))
  {
    $category = $this->model->getItem((object)[
      'table' => 'categories',
      'field' => 'name',
      'where' => 'srl='.(int)$output->data->category_srl,
    ]);
    if ($category->data && $category->data->name)
    {
      $output->data->category_name = $category->data->name;
    }
  }
  // get nest name
  if (isset($output->data->nest_srl) && Util::checkKeyInExtField('nest_name', $this->get->ext_field))
  {
    $nest = $this->model->getItem((object)[
      'table' => 'nests',
      'where' => 'srl='.(int)$output->data->nest_srl,
    ]);
    if (isset($nest->data->name))
    {
      $output->data->nest_name = $nest->data->name;
    }
  }

  // update hit
  if ($this->get->hit && isset($output->data->hit))
  {
    $output->data->hit = $output->data->hit + 1;
    $hit = (int)$output->data->hit;
    $this->model->edit((object)[
      'table' => 'articles',
      'where' => 'srl='.$srl,
      'data' => [ "hit='$hit'" ],
    ]);
  }

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output data
  return Output::data($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::data($e->getMessage(), $e->getCode());
}
