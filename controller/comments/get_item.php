<?php
namespace Core;
use Controller\Main;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * get comment
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
    'table' => 'comments',
    'srl' => $srl,
    'useStrict' => true,
  ]);

  // set output
  $output = Main::item($this, (object)[
    'table' => 'comments',
    'srl' => $srl,
  ]);

  if ($output->data ?? false)
  {
    $ext_field = $this->get->ext_field ?? null;

    // get username
    if (Util::checkKeyInExtField('user_name', $ext_field))
    {
      $user = $this->model->getItem((object)[
        'table' => 'users',
        'field' => 'name',
        'where' => 'srl='.(int)$output->data->user_srl,
      ]);
      $output->data->user_name = $user->data->name;
    }
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
  if (isset($this->model)) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
