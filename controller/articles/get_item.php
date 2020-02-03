<?php
namespace Core;
use Exception, Controller;

if (!defined('__GOOSE__')) exit();

/**
 * get article
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
    'table' => 'articles',
    'srl' => $srl,
    'useStrict' => true,
  ]);

  // set where
  $where = ($app = $_GET['app']) ? ' and app_srl='.$app : '';
  if ($nest = $_GET['nest'])
  {
    $where .= ' and nest_srl='.$nest;
  }
  if ($category = $_GET['category'])
  {
    $where .= ($category === 'null') ? ' and category_srl IS NULL' : ' and category_srl='.$category;
  }
  if ($_GET['visible_type'] === 'all')
  {
    if (!$token->data->admin)
    {
      $user_srl = isset($token->data->user_srl) ? (int)$token->data->user_srl : '';
      $where .= ' and user_srl=\''.$user_srl.'\'';
    }
  }
  else
  {
    $where .= ' and type IS NULL'; // type 필드가 `null`일때 공개된 글입니다.
  }

  // set output
  $output = Controller\Main::item((object)[
    'model' => $this->model,
    'table' => 'articles',
    'srl' => $srl,
    'where' => $where,
    'json_field' => ['json'],
  ]);

  // get category name
  if ($output->data && $output->data->category_srl && Util::checkKeyInExtField('category_name'))
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
  if ($output->data && $output->data->nest_srl && Util::checkKeyInExtField('nest_name'))
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
  if ($_GET['hit'] && isset($output->data->hit))
  {
    $output->data->hit = $output->data->hit + 1;
    $hit = (int)$output->data->hit;
    $this->model->edit((object)[
      'table' => 'articles',
      'where' => 'srl='.$srl,
      'data' => [ "hit='$hit'" ]
    ]);
  }

  // set token
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
