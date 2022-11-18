<?php
namespace Core;
use Exception, Controller\Main;
use Controller\categories\UtilForCategories;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit json
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

  // check post values
  Util::checkExistValue($this->post, [ 'name', 'json' ]);

  // set value
  $json = ($this->post->json ?? false) ? Util::testJsonData($this->post->json) : null;

  // connect db
  $this->model->connect();

  // check access
  $token = Main::checkAccessItem($this, (object)[
    'table' => 'json',
    'srl' => $srl,
  ]);

  if ($this->post->category_srl ?? false)
  {
    $cnt = $this->model->getCount((object)[
      'table' => 'categories',
      'where' => '`module`="'.UtilForCategories::$module['json'].'" and `srl`='.(int)$this->post->category_srl,
      'debug' => __API_DEBUG__,
    ])->data;
    if ($cnt <= 0)
    {
      throw new Exception(Message::make('error.noData', 'categories'));
    }
  }

  // set data
  $data = [];
  if ($this->post->name ?? false) $data[] = "`name`='{$this->post->name}'";
  if ($this->post->description ?? false) $data[] = "`description`='{$this->post->description}'";
  if ($this->post->category_srl ?? false) $data[] = "`category_srl`=".($this->post->category_srl ?: 'null');
  if ($json) $data[] = "`json`='$json'";
  if ($this->post->path ?? false) $data[] = "`path`='".($this->post->path ?? '')."'";
  if (count($data) <= 0)
  {
    throw new Exception(Message::make('error.noEditData'));
  }

  // set output
  $output = Main::edit($this, (object)[
    'table' => 'json',
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
