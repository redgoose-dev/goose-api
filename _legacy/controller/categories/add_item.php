<?php
namespace Core;
use Exception, Controller\Main;
use Controller\categories\UtilForCategories;

if (!defined('__API_GOOSE__')) exit();

/**
 * add category
 *
 * @var Goose|Connect $this
 */

try
{
  // set module
  $module = $this->post->module ?? null;
  if (!in_array($module, UtilForCategories::$module)) throw new Exception('Invalid module');

  // check post values
  Util::checkExistValue($this->post, array_filter([
    'module',
    'name',
    $module === UtilForCategories::$module['article'] ? 'target_srl' : false,
  ]));

  // connect db
  $this->model->connect();

  // check authorization
  $token = Auth::checkAuthorization($this->model, 'user');

  // check exist module data for article
  if ($module === UtilForCategories::$module['article'])
  {
    $cnt = $this->model->getCount((object)[
      'table' => 'nests',
      'where' => 'srl='.(int)$this->post->target_srl,
    ])->data;
    if ($cnt <= 0)
    {
      throw new Exception(Message::make('error.noData', 'module'));
    }
  }

  // get max turn
  $where = match ($module)
  {
    UtilForCategories::$module['article'] => 'target_srl='.(int)$this->post->target_srl,
    UtilForCategories::$module['json'] => 'module="'.UtilForCategories::$module['json'].'"',
  };
  $max = $this->model->getMax((object)[
    'table' => 'categories',
    'field' => 'turn',
    'where' => $where,
  ])->data;

  // set output
  try
  {
    $output = Main::add($this, (object)[
      'table' => 'categories',
      'data' => (object)[
        'srl' => null,
        'target_srl' => $this->post->target_srl ?? null,
        'user_srl' => (int)$token->data->srl,
        'turn' => $max + 1,
        'name' => trim($this->post->name ?? ''),
        'module' => $this->post->module,
        'regdate' => date('Y-m-d H:i:s'),
      ],
    ]);
  }
  catch(Exception $e)
  {
    throw new Exception(Message::make('error.failedAdd', 'category'));
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
