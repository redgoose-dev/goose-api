<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit comment
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
    'table' => 'comments',
    'srl' => $srl,
  ]);

  // check article
  if (isset($this->post->article_srl) && (int)$this->post->article_srl > 0)
  {
    Controller\comments\UtilForComments::checkData(
      $this,
      (int)$this->post->article_srl,
      'articles',
      'article_srl'
    );
  }

  // check user
  if (isset($this->post->user_srl) && (int)$this->post->user_srl > 0)
  {
    Controller\comments\UtilForComments::checkData(
      $this,
      (int)$this->post->user_srl,
      'users',
      'user_srl'
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
  if (isset($this->post->user_srl)) $data[] = "`user_srl`=".$this->post->user_srl;
  if (isset($this->post->content)) $data[] = "`content`='{$this->post->content}'";
  if (count($data) <= 0) throw new Exception(Message::make('error.notFound', 'data'));

  // set output
  $output = Controller\Main::edit($this, (object)[
    'table' => 'comments',
    'srl' => $srl,
    'data' => $data,
  ]);

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
