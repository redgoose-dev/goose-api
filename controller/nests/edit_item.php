<?php
namespace Core;
use Exception, Controller;

if (!defined('__GOOSE__')) exit();

/**
 * edit nest
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

  // check post values
  Util::checkExistValue($_POST, [ 'id', 'name' ]);

  // check `id`
  if (!Text::allowString($_POST['id']))
  {
    throw new Exception(Message::make('error.onlyKeywordType', 'id'));
  }

  // check and set json
  $json = null;
  if (isset($_POST['json']))
  {
    $json = json_decode(urldecode($_POST['json']), false);
    if (!$json)
    {
      throw new Exception(Message::make('error.json'));
    }
    $json = urlencode(json_encode($json, false));
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessItem((object)[
    'model' => $this->model,
    'table' => 'nests',
    'srl' => $srl,
  ]);

  // check duplicate nest id
  $cnt = $this->model->getCount((object)[
    'table' => 'nests',
    'where' => 'id="'.trim($_POST['id']).'" and srl!='.$srl,
  ]);
  if ($cnt->data)
  {
    throw new Exception(Message::make('error.duplicate', 'id'));
  }

  // set output
  $output = Controller\Main::edit((object)[
    'model' => $this->model,
    'table' => 'nests',
    'srl' => $srl,
    'data' => [
      isset($_POST['app_srl']) ? "app_srl='$_POST[app_srl]'" : '',
      isset($_POST['id']) ? "id='".trim($_POST['id'])."'" : '',
      isset($_POST['name']) ? "name='".trim($_POST['name'])."'" : '',
      isset($_POST['description']) ? "description='".trim($_POST['description'])."'" : '',
      isset($_POST['json']) ? "json='$json'" : '',
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
