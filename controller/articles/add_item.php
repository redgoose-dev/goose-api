<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * add article
 *
 * @var Goose|Connect $this
 */

try
{
  // check post values
  Util::checkExistValue($this->post, ['app_srl', 'nest_srl']);

  // check order date
  if ($this->post->order && !preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $this->post->order))
  {
    throw new Exception(Message::make('error.date', 'order'));
  }

  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');

  // check app
  $cnt = $this->model->getCount((object)[
    'table' => 'apps',
    'where' => 'srl='.(int)$this->post->app_srl,
  ]);
  if (!$cnt->data)
  {
    throw new Exception(Message::make('error.noData', 'apps'));
  }

  // check nest
  $cnt = $this->model->getCount((object)[
    'table' => 'nests',
    'where' => 'srl='.(int)$this->post->nest_srl,
  ]);
  if (!$cnt->data)
  {
    throw new Exception(Message::make('error.noData', 'nests'));
  }

  // check category
  if (isset($this->post->category_srl) && (int)$this->post->category_srl > 0)
  {
    $cnt = $this->model->getCount((object)[
      'table' => 'categories',
      'where' => 'srl='.(int)$this->post->category_srl,
    ]);
    if (!$cnt->data)
    {
      throw new Exception(Message::make('error.noData', 'categories'));
    }
  }

  // filtering text
  if (isset($this->post->title))
  {
    $this->post->title = htmlspecialchars(addslashes(trim($this->post->title)));
    $this->post->title = str_replace('&amp;', '&', $this->post->title);
    $this->post->title = str_replace('&quot;', '"', $this->post->title);
    $this->post->title = str_replace('&lt;', '<', $this->post->title);
    $this->post->title = str_replace('&gt;', '>', $this->post->title);
  }
  if (isset($this->post->content) && $this->get->content !== 'raw')
  {
    $this->post->content = addslashes($this->post->content);
  }

  // set output
  $output = Controller\Main::add($this, (object)[
    'table' => 'articles',
    'data' => (object)[
      'srl' => null,
      'app_srl' => (int)$this->post->app_srl ? (int)$this->post->app_srl : null,
      'nest_srl' => (int)$this->post->nest_srl ? (int)$this->post->nest_srl : null,
      'category_srl' => (int)$this->post->category_srl ? (int)$this->post->category_srl : null,
      'user_srl' => (int)$token->data->user_srl,
      'type' => $this->post->type ? $this->post->type : 'public',
      'title' => $this->post->title,
      'content' => $this->post->content,
      'hit' => 0,
      'star' => 0,
      'json' => $this->post->json,
      'ip' => ($_SERVER['REMOTE_ADDR'] !== '::1') ? $_SERVER['REMOTE_ADDR'] : 'localhost',
      'regdate' => date('Y-m-d H:i:s'),
      'modate' => date('Y-m-d H:i:s'),
      'order' => $this->post->order ? date('Y-m-d', strtotime($this->post->order)) : date('Y-m-d'),
    ],
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
