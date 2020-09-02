<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit app
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

	// id check
	if ($this->post->id && !Text::allowString($this->post->id))
	{
    throw new Exception(Message::make('error.onlyKeywordType', 'id'));
	}

  // connect db
  $this->model->connect();

	// check access
	$token = Controller\Main::checkAccessItem($this, (object)[
		'table' => 'apps',
		'srl' => $srl,
	]);

	// check app id
  if (isset($this->post->id))
  {
    $check_id = $this->model->getCount((object)[
      'table' => 'apps',
      'where' => "id LIKE '{$this->post->id}' and srl!=".$srl,
    ]);
    if (!!$check_id->data)
    {
      throw new Exception(Message::make('error.checkSame', 'id'));
    }
  }

	// set output
  $output = Controller\Main::edit($this, (object)[
    'table' => 'apps',
    'srl' => $srl,
    'data' => [
      $this->post->id ? "id='{$this->post->id}'" : '',
      $this->post->name ? "name='{$this->post->name}'" : '',
      $this->post->description ? "description='{$this->post->description}'" : '',
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
