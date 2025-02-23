<?php
namespace Core;
use Exception, Controller\Main;
use Controller\checklist\UtilForChecklist;

if (!defined('__API_GOOSE__')) exit();

/**
 * add checklist item
 *
 * @var Goose|Connect $this
 */

try
{
  // check post values
  Util::checkExistValue($this->post, [ 'content' ]);

  // connect db
  $this->model->connect();

  // check access
  $token = Auth::checkAuthorization($this->model, 'user');

  // adjust content
  $content = UtilForChecklist::adjustContent($this->post->content ?? '');

  // set percent into content
  $percent = UtilForChecklist::getPercentIntoCheckboxes($content);

  // set output
  $output = Main::add($this, (object)[
    'table' => 'checklist',
    'return' => $this->get->return ?? false,
    'data' => (object)[
      'srl' => null,
      'user_srl' => (int)$token->data->srl,
      'content' => $content,
      'percent' => $percent,
      'regdate' => $this->post->regdate ?? date('Y-m-d H:i:s'),
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
  if ($this->model ?? false) $this->model->disconnect();
  return Error::result($e->getMessage(), $e->getCode());
}
