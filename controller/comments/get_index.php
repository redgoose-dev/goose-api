<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * get comments
 *
 * @var Goose|Connect $this
 */

try
{
  // connect db
  $this->model->connect();

  // set where
  $where = '';
  if ($article_srl = $this->get->article) $where .= ' and article_srl='.(int)$article_srl;
  if ($user_srl = $this->get->user) $where .= ' and user_srl='.(int)$user_srl;
  if ($q = $this->get->q) $where .= ' and content LIKE \'%'.$q.'%\'';

  // check access
  $token = Controller\Main::checkAccessIndex($this, false);

  // set output
  $output = Controller\Main::index($this, (object)[
    'table' => 'comments',
    'where' => $where,
  ]);

  // get user name
  if ($output->data && Util::checkKeyInExtField('user_name', $this->get->ext_field))
  {
    $output->data->index = Controller\comments\UtilForComments::getUserName(
      $this,
      $output->data->index
    );
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
