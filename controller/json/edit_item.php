<?php
namespace Core;
use Exception, Controller;

if (!defined('__GOOSE__')) exit();

/**
 * edit json
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
  Util::checkExistValue($_POST, [ 'name', 'json' ]);

  // set value
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
    'table' => 'json',
    'srl' => $srl,
  ]);

  // set output
  $output = Controller\Main::edit((object)[
    'model' => $this->model,
    'table' => 'json',
    'srl' => $srl,
    'data' => [
      $_POST['name'] ? "name='$_POST[name]'" : '',
      $_POST['description'] ? "description='$_POST[description]'" : '',
      $_POST['json'] ? "json='$json'" : '',
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
