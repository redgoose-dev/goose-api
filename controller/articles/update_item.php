<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * update hit or star from article
 *
 * @var Goose|Connect $this
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
  $type = $this->get->type;
  if (!($type === 'hit' || $type === 'star'))
  {
    throw new Exception(Message::make('error.notFound', 'type'));
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessItem($this, (object)[
    'table' => 'articles',
    'srl' => $srl,
    'useStrict' => true,
  ]);

  // get article
  $article = $this->model->getItem((object)[
    'table' => 'articles',
    'field' => 'srl,hit,star',
    'where' => 'srl='.$srl,
    'debug' => __API_DEBUG__,
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
  $output = Controller\Main::edit($this, (object)[
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
  return Output::data($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::data($e->getMessage(), $e->getCode());
}
