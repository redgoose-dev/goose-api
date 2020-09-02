<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit json
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

  // check post values
  Util::checkExistValue($this->post, ['name', 'json']);

  // set value
  $json = null;
  if (isset($this->post->json))
  {
    $json = json_decode(urldecode($this->post->json), false);
    if (!$json)
    {
      throw new Exception(Message::make('error.json'));
    }
    $json = urlencode(json_encode($json, false));
  }

  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessItem($this, (object)[
    'table' => 'json',
    'srl' => $srl,
  ]);

  // set output
  $output = Controller\Main::edit($this, (object)[
    'table' => 'json',
    'srl' => $srl,
    'data' => [
      $this->post->name ? "name='{$this->post->name}'" : '',
      $this->post->description ? "description='{$this->post->description}'" : '',
      $this->post->json ? "json='$json'" : '',
    ],
  ]);

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
