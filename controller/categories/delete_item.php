<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * delete category
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
    'table' => 'categories',
    'srl' => $srl,
  ]);

  // remove category
  $output = Controller::delete((object)[
    'model' => $this->model,
    'table' => 'categories',
    'srl' => $srl,
  ]);

  // set output
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
