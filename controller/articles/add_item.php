<?php
namespace Core;
use Exception, Controller;

if (!defined('__GOOSE__')) exit();

/**
 * add article
 *
 * @var Goose $this
 */

try
{
  // check post values
  Util::checkExistValue($_POST, [ 'app_srl', 'nest_srl', 'title', 'content' ]);

  // check order date
  if ($_POST['order'] && !preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $_POST['order']))
  {
    throw new Exception(Message::make('error.date', 'order'));
  }

  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');

  // filtering text
  $_POST['title'] = htmlspecialchars(addslashes(trim($_POST['title'])));
  $_POST['title'] = str_replace('&amp;', '&', $_POST['title']);
  $_POST['title'] = str_replace('&quot;', '"', $_POST['title']);
  $_POST['title'] = str_replace('&lt;', '<', $_POST['title']);
  $_POST['title'] = str_replace('&gt;', '>', $_POST['title']);
  if ($_GET['content'] !== 'raw')
  {
    $_POST['content'] = addslashes($_POST['content']);
  }

  // check app
  $cnt = $this->model->getCount((object)[
    'table' => 'apps',
    'where' => 'srl='.(int)$_POST['app_srl'],
  ]);
  if (!$cnt->data)
  {
    throw new Exception(Message::make('error.noData', 'apps'));
  }

  // check nest
  $cnt = $this->model->getCount((object)[
    'table' => 'nests',
    'where' => 'srl='.(int)$_POST['nest_srl'],
  ]);
  if (!$cnt->data)
  {
    throw new Exception(Message::make('error.noData', 'nests'));
  }

  // check category
  if ($_POST['category_srl'] && (int)$_POST['category_srl'] > 0)
  {
    $cnt = $this->model->getCount((object)[
      'table' => 'categories',
      'where' => 'srl='.(int)$_POST['category_srl'],
    ]);
    if (!$cnt->data)
    {
      throw new Exception(Message::make('error.noData', 'categories'));
    }
  }

  // set output
  $output = Controller\Main::add((object)[
    'model' => $this->model,
    'table' => 'articles',
    'data' => (object)[
      'srl' => null,
      'app_srl' => (int)$_POST['app_srl'] ? (int)$_POST['app_srl'] : null,
      'nest_srl' => (int)$_POST['nest_srl'] ? (int)$_POST['nest_srl'] : null,
      'category_srl' => (int)$_POST['category_srl'] ? (int)$_POST['category_srl'] : null,
      'user_srl' => (int)$token->data->user_srl,
      'type' => $_POST['type'] ? $_POST['type'] : null,
      'title' => $_POST['title'],
      'content' => $_POST['content'],
      'hit' => 0,
      'star' => 0,
      'json' => $_POST['json'],
      'ip' => ($_SERVER['REMOTE_ADDR'] !== '::1') ? $_SERVER['REMOTE_ADDR'] : 'localhost',
      'regdate' => date('Y-m-d H:i:s'),
      'modate' => date('Y-m-d H:i:s'),
      'order' => $_POST['order'] ? date('Y-m-d', strtotime($_POST['order'])) : date('Y-m-d'),
    ],
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
