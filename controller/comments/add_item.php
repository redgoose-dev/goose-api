<?php
namespace Core;
use Controller\Main, Controller\comments\UtilForComments;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * add comment
 * `content`값의 내용은 `markdown`을 사용하기 때문에 내용 그대로 db에 입력한다.
 *
 * @var Goose|Connect $this
 */

try
{
  // check post values
  Util::checkExistValue($this->post, ['article_srl', 'content']);

  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');

  // check article
  UtilForComments::checkData(
    $this,
    (int)$this->post->article_srl,
    'articles',
    'article_srl'
  );

  // check user
  UtilForComments::checkData(
    $this,
    $token->data->srl,
    'users',
    'user_srl'
  );

  // fix content
  $this->post->content = addslashes($this->post->content ?? '');

  // set output
  $output = Main::add($this, (object)[
    'table' => 'comments',
    'data' => (object)[
      'srl' => null,
      'article_srl' => $this->post->article_srl,
      'user_srl' => $token->data->srl,
      'content' => $this->post->content,
      'regdate' => date('Y-m-d H:i:s'),
    ],
  ]);

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output data
  return Output::result($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
