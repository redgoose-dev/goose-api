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
 * 기초작업을 위한 인스톨
 *
 * @return string
 */
function install()
{
	// TODO: 작업예정
	echo "action install\n";
	Install::basic();
}

/**
 * install db
 * 데이터베이스 설치를 위한 인스톨
 */
function install_db()
{
	// TODO: 작업예정
	echo "action install-db\n";
	Install::db();
}


// switching action
switch ($argv[1])
{
	case 'install':
		install();
		echo "\n";
		break;

	case 'install-db':
		install_db();
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
