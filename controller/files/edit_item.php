<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * edit file
 *
 * @var Goose $this
 */

try
{
	// get values
	$_PATCH = Util::getFormData();

	var_dump($_FILES);

	// TODO: 작업예정

}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}