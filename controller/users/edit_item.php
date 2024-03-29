<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit user
 *
 * @var Goose|Connect $this
 */

try
{
  // check and set srl
  $srl = (int)$this->params['srl'];
  if (!($srl && $srl > 0))
  {
    throw new Exception(Message::make('error.notFound', 'srl'), 404);
  }

  // check and set json
  $json = ($this->post->json ?? false) ? Util::testJsonData($this->post->json) : null;

  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');
  if (!$token->data->admin && ((int)$token->data->srl !== $srl))
  {
    throw new Exception(Message::make('error.access'), 401);
  }

  // check data
  $cnt = $this->model->getCount((object)[
    'table' => 'users',
    'where' => 'srl='.$srl,
  ])->data;
  if ($cnt <= 0)
  {
    throw new Exception(Message::make('error.noData', 'user'), 204);
  }

  // check email address
  if ($this->post->email ?? false)
  {
    $cnt = $this->model->getCount((object)[
      'table' => 'users',
      'where' => 'email="'.$this->post->email.'" and srl!='.$srl,
      'debug' => __API_DEBUG__,
    ])->data;
    if ($cnt > 0)
    {
      throw new Exception(Message::make('error.existsValue', $this->post->email));
    }
    // test email address
    Text::checkEmail($this->post->email);
  }

  // set data
  $data = [];
  if (isset($this->post->email)) $data[] = "email='{$this->post->email}'";
  if (isset($this->post->name)) $data[] = "name='{$this->post->name}'";
  if (isset($this->post->admin) && $token->data->admin) $data[] = "admin=".(int)$this->post->admin;
  if (isset($this->post->json)) $data[] = "json='$json'";
  if (count($data) <= 0)
  {
    throw new Exception(Message::make('error.notFound', 'data'));
  }

  try
  {
    // set output
    $output = Controller\Main::edit($this, (object)[
      'table' => 'users',
      'srl' => $srl,
      'data' => $data,
    ]);
  }
  catch(Exception $e)
  {
    throw new Exception(Message::make('error.failedEdit', 'user'));
  }

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
