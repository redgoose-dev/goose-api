<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * get comment
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

  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessItem($this, (object)[
    'table' => 'comments',
    'srl' => $srl,
    'useStrict' => true,
  ]);

  // set output
  $output = Controller\Main::item($this, (object)[
    'table' => 'comments',
    'srl' => $srl,
  ]);

  // get user name
  if ($output->data && Util::checkKeyInExtField('user_name', $this->get->ext_field))
  {
    $user = $this->model->getItem((object)[
      'table' => 'users',
      'field' => 'name',
      'where' => 'srl='.(int)$output->data->user_srl,
    ]);
    $output->data->user_name = $user->data->name;
  }

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
