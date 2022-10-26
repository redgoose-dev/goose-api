<?php
namespace Core;
use Controller\Main;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * get category
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

  // connect db
  $this->model->connect();

  // check access
  $token = Main::checkAccessItem($this, (object)[
    'table' => 'categories',
    'srl' => $srl,
    'useStrict' => true,
  ]);

  // set output
  $output = Main::item($this, (object)[
    'table' => 'categories',
    'srl' => $srl,
  ]);

  if ($output->data ?? false)
  {
    $ext_field = $this->get->ext_field ?? null;

    // get article count (count_article)
    if (Util::checkKeyInExtField('count_article', $ext_field))
    {
      $where = (!$token->data->admin && $token->data->srl) ? ' and user_srl='.(int)$token->data->srl : '';
      $where .= ' and (NOT type LIKE \'ready\' or type=\'public\')';
      $cnt = $this->model->getCount((object)[
        'table' => 'articles',
        'where' => $where.' and category_srl='.(int)$output->data->srl,
      ])->data;
      $output->data->count_article = $cnt;
    }
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
  if (isset($this->model)) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
