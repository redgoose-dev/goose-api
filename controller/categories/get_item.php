<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

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
  $token = Controller\Main::checkAccessItem((object)[
    'model' => $this->model,
    'table' => 'categories',
    'srl' => $srl,
    'useStrict' => true,
  ]);

  // set output
  $output = Controller\Main::item((object)[
    'model' => $this->model,
    'table' => 'categories',
    'srl' => $srl,
  ]);

  // get article count
  if ($output->data && Util::checkKeyInExtField('count_article'))
  {
    $where = (!$token->data->admin && $token->data->user_srl) ? ' and user_srl='.(int)$token->data->user_srl : '';
    $where .= ' and (NOT type LIKE \'ready\' or type=\'public\')';
    $cnt = $this->model->getCount((object)[
      'table' => 'articles',
      'where' => $where.' and category_srl='.(int)$output->data->srl,
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
