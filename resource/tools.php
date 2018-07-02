<?php
namespace Core;
use Exception;
use Dotenv\Dotenv;

// load autoload
require __DIR__.'/../vendor/autoload.php';

// set static values
define('__DEBUG__', true);
define('__PATH__', __DIR__.'/../');

/**
 * make token
 *
 * @return string
 */
function makeToken()
{
	try
	{
		try
		{
			$dotenv = new Dotenv(__PATH__);
			$dotenv->load();
		}
		catch(Exception $e)
		{
			throw new Exception('.env error');
		}

		// check install
		Install::check();

		// make public token
		$jwt = Token::make((object)[
			'time' => true,
			'exp' => false,
		]);

		// print token
		return $jwt->token;
	}
	catch(Exception $e)
	{
		return $e->getMessage();
	}
}

/**
 * install
 *
 * @return string
 */
function install()
{
	// TODO: 준비작업이 끝나면 작업예정
	echo "action install";
}


// switching action
switch ($argv[1])
{
	case 'install':
		install();
		echo "\n";
		break;

	case 'make-token':
		echo makeToken();
		echo "\n";
		break;

	default:
		echo "ERROR : no argv\n";
		break;
}
