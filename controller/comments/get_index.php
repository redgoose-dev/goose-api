<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * get comments
 *
 * @var Goose $this
 */

try
{
  // connect db
  $this->model->connect();

  // set where
  $where = '';
  if ($article_srl = $_GET['article']) $where .= ' and article_srl='.(int)$article_srl;
  if ($user_srl = $_GET['user']) $where .= ' and user_srl='.(int)$user_srl;
  if ($q = $_GET['q']) $where .= ' and content LIKE \'%'.$q.'%\'';

  // check access
  $token = Controller\Main::checkAccessIndex($this->model, false);

  // set output
  $output = Controller\Main::index((object)[
    'model' => $this->model,
    'table' => 'comments',
    'where' => $where,
  ]);

  // get user name
  if ($output->data && Util::checkKeyInExtField('user_name'))
  {
    $output->data->index = Controller\comments\UtilForComments::getUserName(
      $this->model,
      $output->data->index
    );
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
  if (isset($model)) $model->disconnect();
  Error::data($e->getMessage(), $e->getCode());
}
