<?php
namespace Core;
use Exception, Controller;

if (!defined('__GOOSE__')) exit();

/**
 * get nests
 *
 * @var Goose $this
 */

try
{
  // connect db
  $this->model->connect();

	// set where
	$where = '';
	if ($app = $_GET['app'])
	{
		$where .= ($app === 'null' || $app === 'NULL') ? ' and app_srl IS NULL' : ' and app_srl='.$app;
	}
	if ($id = $_GET['id'])
	{
		$where .= ' and id LIKE \''.$id.'\'';
	}
	if ($name = $_GET['name'])
	{
		$where .= ' and name LIKE \'%'.$name.'%\'';
	}
  if ($user_srl = $_GET['user'])
  {
    $where .= ' and user_srl='.(int)$user_srl;
  }

	// check access
	$token = Controller\Main::checkAccessIndex($this->model, true);
	$where .= (!$token->data->admin && $token->data->user_srl) ? ' and user_srl='.(int)$token->data->user_srl : '';

	// output
	$output = Controller\Main::index((object)[
    'model' => $this->model,
		'table' => 'nests',
		'where' => $where,
		'json_field' => ['json'],
	]);

  if ($output->data && Util::checkKeyInExtField('count_articles'))
  {
    $output->data->index = Controller\nests\UtilForNests::getCountArticles(
      $this->model,
      $output->data->index,
      $token
    );
  }

	// set token
	if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

	// output
	Output::data($output);
}
catch (Exception $e)
{
  $this->model->disconnect();
	Error::data($e->getMessage(), $e->getCode());
}
