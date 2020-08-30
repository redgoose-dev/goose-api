<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * add comment
 * `content`값의 내용은 `markdown`을 사용하기 때문에 내용 그대로 db에 입력한다.
 *
 * @var Goose $this
 */

try
{
  // check post values
  Util::checkExistValue($_POST, [ 'article_srl', 'content' ]);

  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');

  // check article
  Controller\comments\UtilForComments::checkData(
    $this->model,
    $_POST['article_srl'],
    'articles',
    'article_srl'
  );

  // check user
  Controller\comments\UtilForComments::checkData(
    $this->model,
    (int)$token->data->user_srl,
    'users',
    'user_srl'
  );

  // fix content
  $_POST['content'] = addslashes($_POST['content']);

  // set output
  $output = Controller\Main::add((object)[
    'model' => $this->model,
    'table' => 'comments',
    'data' => (object)[
      'srl' => null,
      'article_srl' => $_POST['article_srl'],
      'user_srl' => (int)$token->data->user_srl,
      'content' => $_POST['content'],
      'regdate' => date('Y-m-d H:i:s'),
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
