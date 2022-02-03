<?php
namespace Core;
use Controller\Main, Controller\files\UtilForFiles;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * delete article
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

  // connect db
  $this->model->connect();

	// check access
	$token = Main::checkAccessItem($this, (object)[
		'table' => 'articles',
		'srl' => $srl,
	]);

	// remove thumbnail image
  UtilForFiles::removeThumbnailImage($this, $srl);

	// remove files
  UtilForFiles::removeAttachFiles($this, $srl, 'articles');

	// remove item
	$output = Main::delete($this, (object)[
		'table' => 'articles',
		'srl' => $srl,
	]);

	// set output
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
