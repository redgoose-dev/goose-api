<?php
namespace Core;
use Exception, Controller\Main;
use Controller\categories\UtilForCategories;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit category
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
    'table' => 'categories',
    'srl' => $srl,
  ]);

  // set
  $module = $this->post->module ?? null;
  $target_srl = $this->post->target_srl ?? null;

  // check exist for article
  if ($module === UtilForCategories::$module['article'] && $target_srl)
  {
    $cnt = $this->model->getCount((object)[
      'table' => 'nests',
      'where' => 'srl='.(int)$target_srl,
    ])->data;
    if (($cnt ?? 0) <= 0)
    {
      throw new Exception(Message::make('error.noData', 'module'), 204);
    }
  }

  // set data
  $data = [];
  if ($target_srl) $data[] = '`target_srl`='.(int)$target_srl;
  if ($this->post->name ?? false) $data[] = "`name`='{$this->post->name}'";
  if ($module) $data[] = "`module`='{$module}'";
  if (count($data) <= 0)
  {
    throw new Exception(Message::make('error.noEditData'));
  }

  // edit data
  $output = Main::edit($this, (object)[
    'table' => 'categories',
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
