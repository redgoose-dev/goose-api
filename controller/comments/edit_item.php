<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit comment
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
    'table' => 'comments',
    'srl' => $srl,
  ]);

  // check article
  if ($_POST['article_srl'] && (int)$_POST['article_srl'] > 0)
  {
    Controller\comments\UtilForComments::checkData(
      $this->model,
      $_POST['article_srl'],
      'articles',
      'article_srl'
    );
  }

  // check user
  if ($_POST['user_srl'] && (int)$_POST['user_srl'] > 0)
  {
    Controller\comments\UtilForComments::checkData(
      $this->model,
      $_POST['user_srl'],
      'users',
      'user_srl'
    );
  }

  // fix content
  if (isset($_POST['content']))
  {
    $_POST['content'] = addslashes($_POST['content']);
  }

  // set output
  $output = Controller\Main::edit((object)[
    'model' => $this->model,
    'table' => 'comments',
    'srl' => $srl,
    'data' => [
      $_POST['article_srl'] ? "`article_srl`=".$_POST['article_srl'] : '',
      ($_POST['user_srl']) ? "`user_srl`=".(int)$_POST['user_srl'] : '',
      $_POST['content'] ? "`content`='$_POST[content]'" : '',
    ],
  ]);

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
