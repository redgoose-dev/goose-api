<?php
namespace Core;
use Exception, Controller;

if (!defined('__GOOSE__')) exit();

/**
 * get comment
 *
 * @var Goose $this
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
  $token = Controller\Main::checkAccessItem((object)[
    'model' => $this->model,
    'table' => 'comments',
    'srl' => $srl,
    'useStrict' => true,
  ]);

  // set output
  $output = Controller\Main::item((object)[
    'model' => $this->model,
    'table' => 'comments',
    'srl' => $srl,
  ]);

  // get user name
  if ($output->data && Util::checkKeyInExtField('user_name'))
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
  Output::data($output);
}
catch (Exception $e)
{
  $this->model->disconnect();
  Error::data($e->getMessage(), $e->getCode());
}
