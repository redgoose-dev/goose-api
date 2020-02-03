<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get category
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
    'useStrict' => true,
  ]);

  // set output
  $output = Controller::item((object)[
    'model' => $this->model,
    'table' => 'categories',
    'srl' => $srl,
  ]);

  // get article count
  if ($output->data && Util::checkKeyInExtField('count_article'))
  {
    $cnt = $this->model->getCount((object)[
      'table' => 'articles',
      'where' => 'category_srl='.(int)$output->data->srl,
    ]);
    $output->data->count_article = $cnt->data;
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
