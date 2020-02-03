<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * update hit or star from article
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
  // check type
  $type = $_GET['type'];
  if (!($type === 'hit' || $type === 'star'))
  {
    throw new Exception(Message::make('error.notFound', 'type'));
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Controller::checkAccessItem((object)[
    'model' => $this->model,
    'table' => 'articles',
    'srl' => $srl,
    'useStrict' => true,
  ]);

  // get article
  $article = $this->model->getItem((object)[
    'table' => 'articles',
    'field' => 'srl,hit,star',
    'where' => 'srl='.$srl,
    'debug' => __DEBUG__,
  ]);
  if (!$article->data)
  {
    throw new Exception(Message::make('error.noData', 'article'), 404);
  }

  // set data
  $data = [];
  switch ($type)
  {
    case 'hit':
      $data[] = 'hit='.((int)$article->data->hit + 1);
      break;
    case 'star':
      $data[] = 'star='.((int)$article->data->star + 1);
      break;
  }

  // set output
  $output = Controller::edit((object)[
    'model' => $this->model,
    'table' => 'articles',
    'srl' => $srl,
    'data' => $data,
  ]);

  switch ($type)
  {
    case 'hit':
      $output->data = (object)[ 'hit' => (int)$article->data->hit + 1 ];
      break;
    case 'star':
      $output->data = (object)[ 'star' => (int)$article->data->star + 1 ];
      break;
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
