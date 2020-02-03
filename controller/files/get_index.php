<?php
namespace Core;
use Exception, Controller;

if (!defined('__GOOSE__')) exit();

/**
 * get files
 *
 * url params
 * - @param int article
 * - @param string name
 * - @param string type
 * - @param string ready
 *
 * @var Goose $this
 */

try
{
  // connect db
  $this->model->connect();

	// set where
	$where = '';
	if ($article = $_GET['article'])
	{
		$where .= ' and article_srl='.$article;
	}
	if ($name = $_GET['name'])
	{
		$where .= ' and name LIKE \'%'.$name.'%\'';
	}
	if ($type = $_GET['type'])
	{
		$where .= ' and type LIKE \'%'.$type.'%\'';
	}
	if ($ready = $_GET['ready'])
	{
		switch ($ready)
		{
			case 'true':
				$where .= ' and ready=1';
				break;
			case 'false':
				$where .= ' and ready=0';
				break;
		}
	}

	// check access
	$token = Controller\Main::checkAccessIndex($this->model, true);
	$where .= (!$token->data->admin && $token->data->user_srl) ? ' and user_srl='.(int)$token->data->user_srl : '';

	// set output
	$output = Controller\Main::index((object)[
		'model' => $this->model,
		'table' => 'files',
		'where' => $where,
	]);

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
