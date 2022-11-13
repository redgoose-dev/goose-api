<?php
namespace Core;
use Exception, Controller\Main;
use Controller\comments\UtilForComments;

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
  if ($article_srl = $this->get->article ?? null)
  {
    $where .= ' and article_srl='.(int)$article_srl;
  }
  if ($user_srl = $this->get->user ?? null)
  {
    $where .= ' and user_srl='.(int)$user_srl;
  }
  if ($q = $this->get->q ?? null)
  {
    $where .= ' and content LIKE \'%'.$q.'%\'';
  }

  // check access
  $token = Main::checkAccessIndex($this);

  // set output
  $output = Main::index($this, (object)[
    'table' => 'comments',
    'where' => $where,
  ]);

  $ext_field = $this->get->ext_field ?? null;

  // get username
  if ($output->data && Util::checkKeyInExtField('user_name', $ext_field))
  {
    $output->data->index = UtilForComments::getUserName(
      $this,
      $output->data->index
    );
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
