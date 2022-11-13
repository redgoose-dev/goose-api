<?php
namespace Core;
use Exception, Controller\Main;
use Controller\categories\UtilForCategories;

if (!defined('__API_GOOSE__')) exit();

/**
 * sort turn category
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
    'srls',
    $module === UtilForCategories::$module['article'] ? 'target_srl' : false,
  ]));

  // connect db
  $this->model->connect();

  // set
  $target_srl = $this->post->target_srl ?? null;

  // check access
  if ($module === UtilForCategories::$module['article'])
  {
    $token = Main::checkAccessItem($this, (object)[
      'table' => 'nests',
      'srl' => (int)$target_srl,
    ]);
  }
  else
  {
    $token = Auth::checkAuthorization($this->model, 'user');
  }

  // set srls
  $srls = explode(',', $this->post->srls);

  // update db
  $where = '';
  if ($module === UtilForCategories::$module['article'])
  {
    $where = 'target_srl='.(int)$target_srl.' and ';
  }
  foreach ($srls as $k=>$v)
  {
    $this->model->edit((object)[
      'table' => 'categories',
      'where' => $where.'module="'.$module.'" and srl='.$v,
      'data' => [ 'turn='.($k+1) ],
      'debug' => __API_DEBUG__,
    ]);
  }

  // set output
  $output = (object)[];
  $output->code = 200;

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
