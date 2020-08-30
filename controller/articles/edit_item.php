<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit article
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

  // check order date
  if ($_POST['order'] && !preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $_POST['order']))
  {
    throw new Exception(Message::make('error.date', 'order'));
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessItem((object)[
    'model' => $this->model,
    'table' => 'articles',
    'srl' => $srl,
  ]);

  // filtering text
  if (isset($_POST['title']))
  {
    $_POST['title'] = addslashes(trim($_POST['title']));
  }
  if (isset($_POST['content']) && $_GET['content'] !== 'raw')
  {
    $_POST['content'] = addslashes($_POST['content']);
  }

  // check app_srl
  if ($_POST['app_srl'] && (int)$_POST['app_srl'] > 0)
  {
    $cnt = $this->model->getCount((object)[
      'table' => 'apps',
      'where' => 'srl='.(int)$_POST['app_srl'],
    ]);
    if (!($cnt->data > 0))
    {
      throw new Exception(Message::make('error.noData', 'app_srl'));
    }
  }

  // check nest_srl
  if ($_POST['nest_srl'] && (int)$_POST['nest_srl'] > 0)
  {
    // check nest
    $cnt = $this->model->getCount((object)[
      'table' => 'nests',
      'where' => 'srl='.(int)$_POST['nest_srl'],
    ]);
    if (!($cnt->data > 0))
    {
      throw new Exception(Message::make('error.noData', 'nest_srl'));
    }
  }

  // check category_srl
  if ($_POST['category_srl'] && (int)$_POST['category_srl'] > 0)
  {
    $cnt = $this->model->getCount((object)[
      'table' => 'categories',
      'where' => 'srl='.(int)$_POST['category_srl'],
    ]);
    if (!($cnt->data > 0))
    {
      throw new Exception(Message::make('error.noData', 'category_srl'));
    }
  }

  // set output
  $output = Controller\Main::edit((object)[
    'model' => $this->model,
    'table' => 'articles',
    'srl' => $srl,
    'data' => [
      $_POST['app_srl'] ? "`app_srl`='$_POST[app_srl]'" : '',
      $_POST['nest_srl'] ? "`nest_srl`='$_POST[nest_srl]'" : '',
      $_POST['category_srl'] ? "`category_srl`=$_POST[category_srl]" : '',
      $_POST['user_srl'] ? "`user_srl`='$_POST[user_srl]'" : '',
      isset($_POST['type']) ? "`type`=".($_POST['type'] ? "'$_POST[type]'" : 'public') : '',
      $_POST['title'] ? "`title`='$_POST[title]'" : '',
      $_POST['content'] ? "`content`='$_POST[content]'" : '',
      $_POST['hit'] ? "`hit`='$_POST[hit]'" : '',
      $_POST['star'] ? "`star`='$_POST[star]'" : '',
      $_POST['json'] ? "`json`='$_POST[json]'" : '',
      (isset($_POST['mode']) && $_POST['mode'] === 'add') ? "`regdate`='".date("Y-m-d H:i:s")."'" : '',
      "`modate`='".date("Y-m-d H:i:s")."'",
      isset($_POST['order']) ? "`order`='".($_POST['order'] ? date('Y-m-d', strtotime($_POST['order'])) : date('Y-m-d'))."'" : '',
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
