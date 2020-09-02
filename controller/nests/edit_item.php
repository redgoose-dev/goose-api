<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit nest
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

  // check post values
//  Util::checkExistValue($this->post, [ 'id', 'name' ]);

  // check `id`
  if (isset($this->post->id) && !Text::allowString($this->post->id))
  {
    throw new Exception(Message::make('error.onlyKeywordType', 'id'));
  }

  // check and set json
  $json = null;
  if (isset($this->post->json))
  {
    $json = json_decode(urldecode($this->post->json), false);
    if (!$json)
    {
      throw new Exception(Message::make('error.json'));
    }
    $json = urlencode(json_encode($json, false));
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessItem($this, (object)[
    'table' => 'nests',
    'srl' => $srl,
  ]);

  // check duplicate nest id
  $cnt = $this->model->getCount((object)[
    'table' => 'nests',
    'where' => 'id="'.trim($this->post->id).'" and srl!='.$srl,
  ]);
  if ($cnt->data)
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
  $output = Controller\Main::edit($this, (object)[
    'table' => 'nests',
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
