<?php
namespace Core;
use Exception, Controller;

if (!defined('__GOOSE__')) exit();

/**
 * edit app
 *
 * @var Goose $this
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
	if ($_POST['id'] && !Text::allowString($_POST['id']))
	{
    throw new Exception(Message::make('error.onlyKeywordType', 'id'));
	}

  // connect db
  $this->model->connect();

	// check access
	$token = Controller\Main::checkAccessItem((object)[
		'model' => $this->model,
		'table' => 'apps',
		'srl' => $srl,
	]);

	// check app id
	$check_id = $this->model->getCount((object)[
		'table' => 'apps',
		'where' => "id LIKE '$_POST[id]' and srl!=".$srl,
	]);
	if (!!$check_id->data)
	{
    throw new Exception(Message::make('error.checkSame', 'id'));
	}

	// check category
  // TODO: 이 주석이 뭐지??

	// set output
	try
	{
		$output = Controller\Main::edit((object)[
			'model' => $this->model,
			'table' => 'apps',
			'srl' => $srl,
			'data' => [
				isset($_POST['id']) ? "id='$_POST[id]'" : '',
				isset($_POST['name']) ? "name='$_POST[name]'" : '',
				isset($_POST['description']) ? "description='$_POST[description]'" : '',
			],
		]);
	}
	catch(Exception $e)
	{
		throw new Exception(Message::make('error.failedEdit', 'app'));
	}

	// set token
	if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

	// output data
	Output::data($output);
}
catch (Exception $e)
{
  $this->model->disconnect();
	Error::data($e->getMessage(), $e->getCode());
}
