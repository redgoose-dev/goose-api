<?php
namespace Core;
use Controller\Main, Controller\articles\UtilForArticles;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * get article
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
    'table' => 'articles',
    'srl' => $srl,
    'useStrict' => true,
  ]);

  // set where
  $where = UtilForArticles::getWhereType($this->get->visible_type ?? (($token->data->admin ?? false) ? 'all' : ''));
  // `user_srl`값에 해당되는 값 가져오기
  if (($token->data->srl ?? false) && !($token->data->admin ?? false))
  {
    $where .= ' and user_srl='.(int)$token->data->srl;
  }

  // set output
  $output = Main::item($this, (object)[
    'table' => 'articles',
    'srl' => $srl,
    'where' => $where,
    'field' => $this->get->field ?? '',
    'json_field' => ['json'],
  ]);

  if ($output->data ?? false)
  {
    $ext_field = $this->get->ext_field ?? null;

    // get category name
    if (($output->data->category_srl ?? false) && Util::checkKeyInExtField('category_name', $ext_field))
    {
      $category = $this->model->getItem((object)[
        'table' => 'categories',
        'field' => 'name',
        'where' => 'srl='.(int)$output->data->category_srl,
      ])->data;
      if ($category->name ?? false)
      {
        $output->data->category_name = $category->name;
      }
    }
    // get nest name
    if (($output->data->nest_srl ?? false) && Util::checkKeyInExtField('nest_name', $ext_field))
    {
      $nest = $this->model->getItem((object)[
        'table' => 'nests',
        'where' => 'srl='.(int)$output->data->nest_srl,
      ])->data;
      if ($nest->name ?? false)
      {
        $output->data->nest_name = $nest->name;
      }
    }
    // update hit
    if ((int)($this->get->hit ?? 0) === 1)
    {
      if (($output->data->hit ?? false) !== false)
      {
        $hit = $output->data->hit + 1;
      }
      else
      {
        $itemForHit = $this->model->getItem((object)[
          'table' => 'articles',
          'where' => 'srl='.$srl,
          'field' => 'hit',
        ]);
        $hit = $itemForHit->data->hit + 1;
      }
      $this->model->edit((object)[
        'table' => 'articles',
        'where' => 'srl='.$srl,
        'data' => [ "hit='$hit'" ],
      ]);
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
