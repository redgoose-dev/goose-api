<?php
namespace Core;
use Controller\Main, Controller\comments\UtilForComments;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit comment
 *
 * @var Goose|Connect $this
 */

try
{
  // check and set srl
  if (($srl = (int)($this->params['srl'] ?? 0)) <= 0)
  {
    throw new Exception(Message::make('error.notFound', 'srl'));
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Main::checkAccessItem($this, (object)[
    'table' => 'comments',
    'srl' => $srl,
  ]);

  // check user
  UtilForComments::checkData(
    $this,
    $token->data->srl,
    'users',
    'user_srl'
  );

  // check article
  if ($this->post->article_srl ?? false)
  {
    UtilForComments::checkData(
      $this,
      (int)$this->post->article_srl,
      'articles',
      'article_srl'
    );
  }

  // fix content
  if (isset($this->post->content))
  {
    $this->post->content = addslashes($this->post->content);
  }

  // set data
  $data = [];
  if (isset($this->post->article_srl)) $data[] = "`article_srl`={$this->post->article_srl}";
  if (isset($this->post->content)) $data[] = "`content`='{$this->post->content}'";
  if (count($data) <= 0)
  {
    throw new Exception(Message::make('error.notFound', 'data'));
  }

  // set output
  $output = Main::edit($this, (object)[
    'table' => 'comments',
    'srl' => $srl,
    'data' => $data,
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
