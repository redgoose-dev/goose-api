<?php
namespace Core;
use Exception, Controller;

if (!defined('__GOOSE__')) exit();

/**
 * delete article
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

  // connect db
  $this->model->connect();

	// check access
	$token = Controller\Main::checkAccessItem((object)[
		'model' => $this->model,
		'table' => 'articles',
		'srl' => $srl,
	]);

	// remove thumbnail image
  Controller\files\UtilForFiles::removeThumbnailImage($this->model, $srl);

	// remove files
  Controller\files\UtilForFiles::removeAttachFiles($this->model, $srl);

	// remove item
	$output = Controller\Main::delete((object)[
		'model' => $this->model,
		'table' => 'articles',
		'srl' => $srl,
	]);

	// set output
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
