<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * add app
 *
 * @var Goose|Connect $this
 */

try
{
  // check post values
  Util::checkExistValue($this->post, [ 'id', 'name' ]);

  // check `id`
  if (!Text::allowString($this->post->id, null))
  {
    throw new Exception(Message::make('error.onlyKeywordType', 'id'));
  }

  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');

  // check id
  $cnt = $this->model->getCount((object)[
    'table' => 'apps',
    'where' => "id LIKE '{$this->post->id}'",
  ])->data;
  if ($cnt > 0)
  {
    throw new Exception(Message::make('error.checkSame', 'id'));
  }

  // set output
  $output = Controller\Main::add($this, (object)[
    'table' => 'apps',
    'data' => (object)[
      'srl' => null,
      'user_srl' => (int)$token->data->srl,
      'id' => $this->post->id ?? null,
      'name' => $this->post->name ?? null,
      'description' => $this->post->description ?? null,
      'regdate' => date('Y-m-d H:i:s'),
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
  if (isset($this->model)) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
