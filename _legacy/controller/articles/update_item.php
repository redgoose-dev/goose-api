<?php
namespace Core;
use Exception, Controller\Main;

if (!defined('__API_GOOSE__')) exit();

/**
 * update hit or star from article
 *
 * @var Goose|Connect $this
 */

try
{
  // check and set srl
  if (($srl = (int)($this->params['srl'] ?? 0)) <= 0)
  {
    throw new Exception(Message::make('error.notFound', 'srl'));
  }
  // check type
  $type = $this->post->type ?? '';
  if (!($type === 'hit' || $type === 'star'))
  {
    throw new Exception(Message::make('error.notFound', 'type'), 204);
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Main::checkAccessItem($this, (object)[
    'table' => 'articles',
    'srl' => $srl,
    'useStrict' => true,
  ]);

  // get article
  $article = $this->model->getItem((object)[
    'table' => 'articles',
    'field' => 'srl,hit,star',
    'where' => 'srl='.$srl,
  ])->data;
  if (!$article)
  {
    throw new Exception(Message::make('error.noData', 'article'), 404);
  }

  // set data
  $data = [];
  switch ($type)
  {
    case 'hit':
    case 'star':
      $data[] = "$type=".((int)$article->{$type} + 1);
      break;
    default:
      break;
  }

  // set output
  $output = Main::edit($this, (object)[
    'table' => 'articles',
    'srl' => $srl,
    'data' => $data,
  ]);

  switch ($type)
  {
    case 'hit':
    case 'star':
      $output->data = (object)[ $type => (int)$article->{$type} + 1 ];
      break;
    default:
      break;
  }

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output data
  return Output::result($output);
}
catch (Exception $e)
{
  if ($this->model ?? false) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
