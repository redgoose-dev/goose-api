<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * add checklist item
 *
 * @var Goose|Connect $this
 */

try
{
  // check post values
  Util::checkExistValue($this->post, [ 'content' ]);

  // connect db
  $this->model->connect();

  // check access
  $token = Auth::checkAuthorization($this->model, 'user');

  // set output
  $output = Controller\Main::add($this, (object)[
    'table' => 'checklist',
    'data' => (object)[
      'srl' => null,
      'user_srl' => (int)$token->data->user_srl,
      'content' => $this->post->content,
      'percent' => 0,
      'regdate' => isset($this->post->regdate) ? $this->post->regdate : date('Y-m-d'),
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
