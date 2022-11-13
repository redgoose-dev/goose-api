<?php
namespace Core;
use Exception, Controller\Main;
use Controller\json\UtilForJson;

if (!defined('__API_GOOSE__')) exit();

/**
 * get json item
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
    'table' => 'json',
    'srl' => $srl,
    'useStrict' => true,
  ]);

  // set output
  $output = Main::item($this, (object)[
    'table' => 'json',
    'srl' => $srl,
    'json_field' => [ 'json' ],
  ]);

  if ($output->data)
  {
    $ext_field = $this->get->ext_field ?? null;
    // get category name
    if (($output->data->category_srl ?? false) && Util::checkKeyInExtField('category_name', $ext_field))
    {
      $output->data = UtilForJson::extendCategoryNameInItem($this, $output->data);
    }
  }

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output data
  return Output::result($output);
}
catch(Exception $e)
{
  if ($this->model ?? false) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
