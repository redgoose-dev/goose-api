<?php
namespace Core;
use Exception, Controller;

if (!defined('__GOOSE__')) exit();

/**
 * add app
 *
 * @var Goose $this
 */

try
{
  // check post values
  Util::checkExistValue($_POST, [ 'id', 'name' ]);

  // check `id`
  if (!Text::allowString($_POST['id']))
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
    'where' => "id LIKE '$_POST[id]'",
  ]);
  if (!!$cnt->data)
  {
    throw new Exception(Message::make('error.checkSame', 'id'));
  }

  // set output
  $output = Controller\Main::add((object)[
    'model' => $this->model,
    'table' => 'apps',
    'data' => (object)[
      'srl' => null,
      'user_srl' => (int)$token->data->user_srl,
      'id' => $_POST['id'],
      'name' => $_POST['name'],
      'description' => $_POST['description'],
      'regdate' => date('Y-m-d H:i:s'),
    ]
  ]);

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
