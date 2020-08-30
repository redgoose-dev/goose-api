<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * get nest
 *
 * @var Goose $this
 */

try
{
  if ($this->params['srl'] && (int)$this->params['srl'] > 0)
  {
    $srl = (int)$this->params['srl'];
  }
  else if ($this->params['id'])
  {
    $id = $this->params['id'];
  }
  else
  {
    throw new Exception(Message::make('noItems_or', 'srl', 'id'));
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessItem((object)[
    'model' => $this->model,
    'table' => 'nests',
    'srl' => isset($srl) ? $srl : null,
    'id' => isset($id) ? $id : null,
    'useStrict' => true,
  ]);

  // set output
  $output = Controller\Main::item((object)[
    'model' => $this->model,
    'table' => 'nests',
    'srl' => isset($srl) ? $srl : null,
    'id' => isset($id) ? $id : null,
    'json_field' => ['json'],
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
