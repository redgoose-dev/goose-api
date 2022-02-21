<?php
namespace Core;
use Controller\Main;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit nest
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

  // check `id`
  if (isset($this->post->id) && !Text::allowString($this->post->id, null))
  {
    throw new Exception(Message::make('error.onlyKeywordType', 'id'));
  }

  // set json
  if ($json = $this->post->json ?? null) $json = Util::testJsonData($json);

  // connect db
  $this->model->connect();

  // check access
  $token = Main::checkAccessItem($this, (object)[
    'table' => 'nests',
    'srl' => $srl,
  ]);

  // check duplicate nest id
  $cnt = $this->model->getCount((object)[
    'table' => 'nests',
    'where' => 'id="'.trim($this->post->id ?? '').'" and srl!='.$srl,
  ])->data;
  if ($cnt > 0)
  {
    throw new Exception(Message::make('error.duplicate', 'id'));
  }

  // set data
  $data = [];
  if (isset($this->post->app_srl)) $data[] = "app_srl='{$this->post->app_srl}'";
  if (isset($this->post->id)) $data[] = "id='".trim($this->post->id)."'";
  if (isset($this->post->name)) $data[] = "name='".trim($this->post->name)."'";
  if (isset($this->post->description)) $data[] = "description='".trim($this->post->description)."'";
  if (isset($this->post->json)) $data[] = "json='$json'";
  if (count($data) <= 0) throw new Exception(Message::make('error.notFound', 'data'));

  // set output
  $output = Main::edit($this, (object)[
    'table' => 'nests',
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
