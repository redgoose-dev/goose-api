<?php
namespace Core;
use Exception;


class Install {

	/**
	 * error
	 *
	 * @param string $message
	 */
	static private function error($message)
	{
		echo "ERROR: $message\n";
		exit;
	}

	/**
	 * output
	 *
	 * @param string
	 */
	static private function output($str)
	{
		$out = "=====================================================\n";
		$out .= "$str\n";
		$out .= "=====================================================\n";
		echo $out;
		exit;
	}

	/**
	 * check writabled path
	 *
	 * @param string $dir
	 */
	static private function checkWritabledPath($dir=null)
	{
		$dir = $dir ? $dir : __PATH__;
		// check main dir
		if (!is_writable($dir))
		{
			self::error("Please check your permissions. path: `$dir`");
		}
	}

	/**
	 * Check installed
	 *
	 * @throws Exception
	 */
	static public function check()
	{
		// check `/data`
		if (!is_dir(__PATH__.'/data'))
		{
			throw new Exception('The directory `/data` does not exist.');
		}
		if (!is_writable(__PATH__.'/data'))
		{
			throw new Exception('The `/data` directory permission is invalid.');
		}

		// check `/data/upload`
		if (!is_dir(__PATH__.'/data/upload'))
		{
			throw new Exception('The directory `/data/upload` does not exist.');
		}
		if (!is_writable(__PATH__.'/data/upload'))
		{
			throw new Exception('The `/data/upload` directory permission is invalid.');
		}

		// check `/data/settings`
		if (!is_dir(__PATH__.'/data/settings'))
		{
			throw new Exception('The directory `/data/settings` does not exist.');
		}
		if (!is_writable(__PATH__.'/data/settings'))
		{
			throw new Exception('The `/data/settings` directory permission is invalid.');
		}

		// check env
//		TODO: .env 스펙이 확정되면 검사하기
//		TODO: .env.example 파일을 .env로 복제하기. 아니면 인스톨에서 모든것을 물어보는 cli 만들어야함.
//		$env_values = [
//			'SERVICE_NAME',
//			'PATH_URL',
//			'PATH_ROOT',
//			'DB_HOST',
//			'DB_PORT',
//			'DB_DATABASE',
//			'DB_USERNAME',
//			'DB_PASSWORD',
//			'TABLE_PREFIX',
//			'API_DEBUG',
//			'TIMEZONE',
//		];
//		foreach ($env_values as $o)
//		{
//			if (!getenv($o)) return false;
//		}
	}

	/**
	 * basic install
	 */
	static public function basic()
	{
		// check main dir
		self::checkWritabledPath();

		// check exist `.env`
		if (file_exists(__PATH__.'/.env'))
		{
			echo "The `.env` file exists. Do you want to proceed? (y/N) ";
			$ask = fgets(STDIN);
			if (trim(strtolower($ask)) !== 'y')
			{
				echo "Canceled install\n";
				exit;
			}
		}

		// copy .env file
		if (!copy(__PATH__.'/resource/.env.example', __PATH__.'/.env'))
		{
			self::error('Can not copy the `.env` file.');
		}

		// output
		$output = "Success!\n";
		$output .= "Please proceed to the next step.\n";
		$output .= "1) Please edit the `.env` file in a text editor.\n";
		$output .= "2) Run `script.sh install-db` in the command.";
		self::output($output);
	}

	/**
	 * database
	 */
	static public function db()
	{
		// TODO: 작업예정
		echo "install database";
	}
}