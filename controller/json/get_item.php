<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get json item
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
  $token = Controller::checkAccessItem((object)[
    'model' => $this->model,
    'table' => 'json',
    'srl' => $srl,
    'useStrict' => true,
  ]);

  // set output
  $output = Controller::item((object)[
    'model' => $this->model,
    'table' => 'json',
    'srl' => $srl,
    'json_field' => ['json'],
  ]);

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output data
  Output::data($output);
}
catch(Exception $e)
{
  $this->model->disconnect();
  Error::data($e->getMessage(), $e->getCode());
}
