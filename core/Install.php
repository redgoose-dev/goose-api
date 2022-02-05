<?php
namespace Core;
use Dotenv\Dotenv, Exception;


class Install {

  /**
   * error
   *
   * @param string $message
   */
  static private function error(string $message): void
  {
    echo "ERROR: $message\n";
    exit;
  }

  /**
   * output
   *
   * @param string
   */
  static private function output(string $str): void
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
  static private function checkDirectoryPath(string $dir='')
  {
    $dir = $dir ? $dir : __API_PATH__;
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
    if (!$_ENV['API_SERVICE_NAME']) throw new Exception('The value `API_SERVICE_NAME` does not exist.');
    if (!$_ENV['API_TOKEN_KEY']) throw new Exception('The value `API_TOKEN_KEY` does not exist.');
    if (!$_ENV['API_TOKEN_ID']) throw new Exception('The value `API_TOKEN_ID` does not exist.');
    if (!$_ENV['API_PATH_URL']) throw new Exception('The value `API_PATH_URL` does not exist.');
  }

  /**
   * ready for install
   */
  static public function ready()
  {
    $output = '';

    // check main dir
    self::checkDirectoryPath();

    // make data directory
    if (!(is_dir(__API_PATH__.'/data/upload') && is_writable(__API_PATH__.'/data/upload')))
    {
      try
      {
        Util::createDirectory(__API_PATH__.'/data', 0707);
        Util::createDirectory(__API_PATH__.'/data/upload', 0707);
      }
      catch(Exception $e)
      {
        $output .= "ERROR: ".$e->getMessage()."\n";
      }
    }

    // check exist `.env`
    if (file_exists(__API_PATH__.'/.env'))
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
    if (!copy(__API_PATH__.'/resource/.env.example', __API_PATH__.'/.env'))
    {
      self::error('Can not copy the `.env` file.');
    }

    // output
    $output .= "Success!\n";
    $output .= "Please proceed to the next step.\n";
    $output .= "1) Add user and database in MYSQL.\n";
    $output .= "2) Please edit the `.env` file in a text editor.\n";
    $output .= "3) Run `cmd.sh install` in the command.";
    self::output($output);
  }

  /**
   * install
   */
  static public function install()
  {
    $defaultEmail = 'root@goo.se';
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
        $dotenv = Dotenv::createImmutable(__API_PATH__);
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
      if (!(is_dir(__API_PATH__.'/data/upload') && is_writable(__API_PATH__.'/data/upload')))
      {
        throw new Exception('Not found `/data` or `/data/upload`');
      }

      // set default timezone
      if ($_ENV['API_TIMEZONE'])
      {
        date_default_timezone_set($_ENV['API_TIMEZONE']);
      }

      // make tables
      try
      {
        $sql = file_get_contents(__API_PATH__.'/resource/db.default.sql');
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
          'table' => 'users',
          'data' => (object)[
            'srl' => null,
            'email' => $defaultEmail,
            'name' => $defaultName,
            'password' => Text::createPassword($defaultPassword),
            'admin' => 1,
            'regdate' => date('Y-m-d H:i:s')
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
        'host' => Util::getHost($_ENV['API_PATH_URL']),
      ]);

      // destination
      if ($out) $out .= "\n";
      $out .= "Success install!\n";
      $out .= "\n";
      $out .= "* URL guide\n";
      $out .= $_ENV['API_PATH_URL']."\n";
      $out .= "\n";
      $out .= "* Root account guide\n";
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
