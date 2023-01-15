<?php
namespace Core;
use Exception, Controller\Main;
use Controller\articles\UtilForArticles;

if (!defined('__API_GOOSE__')) exit();

/**
 * add article
 *
 * @var Goose|Connect $this
 */

try
{
  // check post values
  Util::checkExistValue($this->post, [ 'app_srl', 'nest_srl' ]);

  // check order date
  if (isset($this->post->order) && !UtilForArticles::checkOrderDate($this->post->order))
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
  ])->data;
  if ($cnt <= 0)
  {
    throw new Exception(Message::make('error.noData', 'apps'), 204);
  }

  // check nest
  $cnt = $this->model->getCount((object)[
    'table' => 'nests',
    'where' => 'srl='.(int)$this->post->nest_srl,
  ])->data;
  if ($cnt <= 0)
  {
    throw new Exception(Message::make('error.noData', 'nests'), 204);
  }

  // check category
  if ((int)($this->post->category_srl ?? 0) > 0)
  {
    $cnt = $this->model->getCount((object)[
      'table' => 'categories',
      'where' => 'srl='.(int)$this->post->category_srl,
    ])->data;
    if ($cnt <= 0)
    {
      throw new Exception(Message::make('error.noData', 'categories'), 204);
    }
  }

  // filtering text
  if ($this->post->title ?? false)
  {
    $this->post->title = addslashes(trim($this->post->title));
  }
  if (isset($this->post->content) && ($this->get->content ?? '') !== 'raw')
  {
    $this->post->content = addslashes($this->post->content);
  }

  // check json
  if ($json = $this->post->json ?? null) $json = Util::testJsonData($json);

  // set output
  $output = Main::add($this, (object)[
    'table' => 'articles',
    'data' => (object)[
      'srl' => null,
      'app_srl' => $this->post->app_srl ?? null,
      'nest_srl' => $this->post->nest_srl ?? null,
      'category_srl' => $this->post->category_srl ?? null,
      'user_srl' => $token->data->srl ?? null,
      'type' => UtilForArticles::getPostType($this->post->type ?? ''),
      'title' => $this->post->title ?? '',
      'content' => $this->post->content ?? '',
      'hit' => 0,
      'star' => 0,
      'json' => $json,
      'ip' => ($_SERVER['REMOTE_ADDR'] !== '::1') ? $_SERVER['REMOTE_ADDR'] : 'localhost',
      'regdate' => date('Y-m-d H:i:s'),
      'modate' => date('Y-m-d H:i:s'),
      'order' => ($this->post->order ?? false) ? date('Y-m-d', strtotime($this->post->order)) : date('Y-m-d'),
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
  if ($this->model ?? false) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
