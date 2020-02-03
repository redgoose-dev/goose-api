<?php
namespace Core;
use Controller\Files\UtilForFiles;
use Exception;

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
	$token = Controller::checkAccessItem((object)[
		'model' => $this->model,
		'table' => 'articles',
		'srl' => $srl,
	]);

	// remove thumbnail image
  UtilForFiles::removeThumbnailImage($this->model, $srl);

	// remove files
  UtilForFiles::removeAttachFiles($this->model, $srl);

	// remove item
	$output = Controller::delete((object)[
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
