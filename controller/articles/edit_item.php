<?php
namespace Core;
use Exception, Controller\Main;
use Controller\articles\UtilForArticles;
use Controller\categories\UtilForCategories;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit article
 *
 * @var Goose|Connect $this
 */

try
{
  // check and set srl
  if (($srl = (int)($this->params['srl'] ?? 0)) <= 0)
  {
    throw new Exception(Message::make('error.notFound', 'srl'), 204);
  }
  // check order date
  if (isset($this->post->order) && !UtilForArticles::checkOrderDate($this->post->order))
  {
    throw new Exception(Message::make('error.date', 'order'));
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Main::checkAccessItem($this, (object)[
    'table' => 'articles',
    'srl' => $srl,
  ]);

  // filtering text
  if ($this->post->title ?? false)
  {
    $this->post->title = addslashes(trim($this->post->title));
  }
  if (isset($this->post->content) && ($this->get->content ?? '') !== 'raw')
  {
    $this->post->content = addslashes($this->post->content);
  }

  // check app_srl
  if ((int)($this->post->app_srl ?? 0) > 0)
  {
    $cnt = $this->model->getCount((object)[
      'table' => 'apps',
      'where' => 'srl='.(int)$this->post->app_srl,
    ])->data;
    if ($cnt <= 0)
    {
      throw new Exception(Message::make('error.noData', 'app_srl'));
    }
  }

  // check nest_srl
  if ((int)($this->post->nest_srl ?? 0) > 0)
  {
    // check nest
    $cnt = $this->model->getCount((object)[
      'table' => 'nests',
      'where' => 'srl='.(int)$this->post->nest_srl,
    ])->data;
    if ($cnt <= 0)
    {
      throw new Exception(Message::make('error.noData', 'nest_srl'));
    }
  }

  // check category_srl
  if ((int)($this->post->category_srl ?? 0) > 0)
  {
    $cnt = $this->model->getCount((object)[
      'table' => 'categories',
      'where' => 'module="'.UtilForCategories::$module['article'].'" and srl='.(int)$this->post->category_srl,
    ])->data;
    if ($cnt <= 0)
    {
      throw new Exception(Message::make('error.noData', 'category_srl'), 204);
    }
  }

  // check json
  if ($json = $this->post->json ?? null) $json = Util::testJsonData($json);

  // set type
  if ($type = $this->post->type ?? '')
  {
    $type = UtilForArticles::getPostType($this->post->type ?? '');
  }

  // set data
  $data = [];
  if (isset($this->post->app_srl)) $data[] = "`app_srl`={$this->post->app_srl}";
  if (isset($this->post->nest_srl)) $data[] = "`nest_srl`={$this->post->nest_srl}";
  if (isset($this->post->category_srl)) $data[] = '`category_srl`='.($this->post->category_srl ?: 'null');
  if (isset($this->post->type)) $data[] = "`type`='$type'";
  if (isset($this->post->title)) $data[] = "`title`='{$this->post->title}'";
  if (isset($this->post->content)) $data[] = "`content`='{$this->post->content}'";
  if (isset($this->post->hit)) $data[] = "`hit`='{$this->post->hit}'";
  if (isset($this->post->star)) $data[] = "`star`='{$this->post->star}'";
  if (isset($this->post->json)) $data[] = "`json`='$json'";
  if (($this->post->mode ?? '') === 'add') $data[] = "`regdate`='".date("Y-m-d H:i:s")."'";
  if (isset($this->post->order))
  {
    $data[] = "`order`='".($this->post->order ? date('Y-m-d', strtotime($this->post->order)) : date('Y-m-d'))."'";
  }
  if (count($data) <= 0)
  {
    throw new Exception(Message::make('error.noEditData'));
  }
  $data[] = "`modate`='".date("Y-m-d H:i:s")."'";

  // set output
  $output = Main::edit($this, (object)[
    'table' => 'articles',
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
  if ($this->model ?? false) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
