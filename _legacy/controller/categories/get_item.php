<?php
namespace Core;
use Exception, Controller\Main;
use Controller\categories\UtilArticlesForCategories;
use Controller\categories\UtilJsonForCategories;
use Controller\categories\UtilForCategories;

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

  if (($output->data ?? false) && ($ext_field = $this->get->ext_field ?? null))
  {
    // get article count (count)
    if (Util::checkKeyInExtField('count', $ext_field))
    {
      switch ($output->data->module ?? null)
      {
        case UtilForCategories::$module['article']:
          $output->data->count_article = UtilArticlesForCategories::extendCountInItem($this, $token, $output->data->srl);
          break;
        case UtilForCategories::$module['json']:
          // TODO: 확장작업
          // $output->data->count_json = UtilJsonForCategories::
          break;
      }
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
  if ($this->model ?? false) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
