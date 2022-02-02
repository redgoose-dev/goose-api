<?php
namespace Core;
use Controller\Main, Controller\checklist\UtilForChecklist;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit checklist item
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

  // check post values
  Util::checkExistValue($this->post, [ 'content' ]);

  // connect db
  $this->model->connect();

  // check access
  $token = Main::checkAccessItem($this, (object)[
    'table' => 'checklist',
    'srl' => $srl,
  ]);

  // adjust content
  $content = UtilForChecklist::adjustContent($this->post->content);

  // set percent into content
  $percent = UtilForChecklist::getPercentIntoCheckboxes($content);

  // set output
  $output = Main::edit($this, (object)[
    'table' => 'checklist',
    'srl' => $srl,
    'data' => [
      "content='{$content}'",
      "percent={$percent}",
    ],
  ]);

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
