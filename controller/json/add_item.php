<?php
namespace Core;
use Exception, Controller\Main;
use Controller\categories\UtilForCategories;

if (!defined('__API_GOOSE__')) exit();

/**
 * add json
 *
 * @var Goose|Connect $this
 */

try
{
	// check post values
	Util::checkExistValue($this->post, [ 'name', 'json' ]);

	// set json
  $json = ($this->post->json ?? false) ? Util::testJsonData($this->post->json) : null;

  // connect db
  $this->model->connect();

	// check authorization
	$token = Auth::checkAuthorization($this->model, 'user');

  // check category
  if ((int)($this->post->category_srl ?? 0) > 0)
  {
    $cnt = $this->model->getCount((object)[
      'table' => 'categories',
      'where' => 'module="'.UtilForCategories::$module['json'].'" and srl='.(int)$this->post->category_srl,
      'debug' => __API_DEBUG__,
    ])->data;
    if ($cnt <= 0)
    {
      throw new Exception(Message::make('error.noData', 'categories'));
    }
  }

	// set output
	try
	{
		$output = Main::add($this, (object)[
			'table' => 'json',
			'data' => (object)[
				'srl' => null,
				'user_srl' => $token->data->srl,
        'category_srl' => $this->post->category_srl ?? null,
				'name' => $this->post->name ?? null,
				'description' => $this->post->description ?? null,
				'json' => $json,
				'regdate' => date('Y-m-d H:i:s'),
			],
		]);
	}
	catch(Exception $e)
	{
    throw new Exception(Message::make('error.failedAdd', 'json'));
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
