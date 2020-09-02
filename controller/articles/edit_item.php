<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit article
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
  // check order date
  if ($this->post->order && !preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $this->post->order))
  {
    throw new Exception(Message::make('error.date', 'order'));
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessItem($this, (object)[
    'table' => 'articles',
    'srl' => $srl,
  ]);

  // filtering text
  if (isset($this->post->title) && $this->post->title)
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

  // check category_srl
  if ($this->post->category_srl && (int)$this->post->category_srl > 0)
  {
    $cnt = $this->model->getCount((object)[
      'table' => 'categories',
      'where' => 'srl='.(int)$this->post->category_srl,
    ]);
    if (!($cnt->data > 0))
    {
      throw new Exception(Message::make('error.noData', 'category_srl'));
    }
  }

  // set output
  $output = Controller\Main::edit($this, (object)[
    'table' => 'articles',
    'srl' => $srl,
    'data' => [
      $this->post->category_srl ? "`category_srl`={$this->post->category_srl}" : '',
      isset($this->post->type) ? "`type`=".($this->post->type ? "'{$this->post->type}'" : 'public') : '',
      $this->post->title ? "`title`='{$this->post->title}'" : '',
      $this->post->content ? "`content`='{$this->post->content}'" : '',
      $this->post->hit ? "`hit`='{$this->post->hit}'" : '',
      $this->post->star ? "`star`='{$this->post->star}'" : '',
      $this->post->json ? "`json`='{$this->post->json}'" : '',
      (isset($this->post->mode) && $this->post->mode === 'add') ? "`regdate`='".date("Y-m-d H:i:s")."'" : '',
      "`modate`='".date("Y-m-d H:i:s")."'",
      isset($this->post->order) ? "`order`='".($this->post->order ? date('Y-m-d', strtotime($this->post->order)) : date('Y-m-d'))."'" : '',
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
