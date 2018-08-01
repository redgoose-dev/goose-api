<?php
namespace Core;
use Dotenv\Dotenv, Exception;


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
	static private function checkDirectoryPath($dir=null)
	{
		$dir = $dir ? $dir : __PATH__;
		if (!is_dir($dir))
		{
			self::error("Directory does not exist. path: `$dir`");
		}
		// check writable path
		if (!is_writable($dir))
		{
			self::error("Please check your permissions. path: `$dir`");
		}
	}

	/**
	 * check env values
	 * `.env`속의 값들중에 필수요소값들을 겁사
	 *
	 * @throws Exception
	 */
	static private function checkEnvValues()
	{
		if (!getenv('SERVICE_NAME')) throw new Exception('The value `SERVICE_NAME` does not exist.');
		if (!getenv('TOKEN_KEY')) throw new Exception('The value `TOKEN_KEY` does not exist.');
		if (!getenv('TOKEN_ID')) throw new Exception('The value `TOKEN_ID` does not exist.');
		if (!getenv('PATH_URL')) throw new Exception('The value `PATH_URL` does not exist.');
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
	}

	/**
	 * ready for install
	 */
	static public function ready()
	{
		// check main dir
		self::checkDirectoryPath();

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
		$output .= "2) Run `script.sh install` in the command.";
		self::output($output);
	}

	/**
	 * install
	 */
	static public function install()
	{
		$defaultEmail = 'root@goose';
		$defaultName = 'root';
		$defaultPassword = '1234';
		try
		{
			$out = '';

			// check main dir
			self::checkDirectoryPath();

			// set dotenv
			try
			{
				$dotenv = new Dotenv(__PATH__);
				$dotenv->load();
			}
			catch(Exception $e)
			{
				throw new Exception('.env error');
			}

			// check env values
			self::checkEnvValues();

			// check connect db
			$model = new Model();
			$model->connect();
			if (!$model->db) throw new Exception('Not found db object');

			// make directories
			try
			{
				Util::createDirectory(__PATH__.'/data', 0707);
				Util::createDirectory(__PATH__.'/data/upload', 0707);
			}
			catch(Exception $e)
			{
				$out .= "ERROR: ".$e->getMessage()."\n";
			}

			// set default timezone
			if (getenv('TIMEZONE'))
			{
				date_default_timezone_set(getenv('TIMEZONE'));
			}

			// make tables
			try
			{
				$sql = file_get_contents(__PATH__.'/resource/db.default.sql');
				$qr = $model->db->exec($sql);
				if ($qr) throw new Exception('error');
			}
			catch(Exception $e)
			{
				$out .= "ERROR: Failed create table\n";
			}

			// add admin user
			try
			{
				$model->add((object)[
					'table' => 'user',
					'data' => (object)[
						'srl' => null,
						'email' => $defaultEmail,
						'name' => $defaultName,
						'pw' => password_hash($defaultPassword, PASSWORD_DEFAULT),
						'admin' => 2,
						'regdate' => date('YmdHis')
					],
					'debug' => true
				]);
			}
			catch (Exception $e)
			{
				$out .= "ERROR: Failed add root user\n";
			}

			// make public token
			$jwt = Token::make((object)[
				'time' => true,
				'exp' => false,
			]);

			// destination
			if ($out) $out .= "\n";
			$out .= "Success install!\n";
			$out .= "\n";
			$out .= "* Manager url\n";
			$out .= getenv('PATH_URL')."/manager\n";
			$out .= "\n";
			$out .= "* The root account\n";
			$out .= "- e-mail : $defaultEmail\n";
			$out .= "- name : $defaultName\n";
			$out .= "- password : $defaultPassword\n";
			$out .= "\n";
			$out .= "* Public token\n";
			$out .= "$jwt->token\n";
			$out .= "\n";
			$out .= "Please change your root account email and password.";
			self::output($out);
		}
		catch(Exception $e)
		{
			self::error($e->getMessage());
		}
	}

}