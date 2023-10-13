<?php
namespace Core;
use Dotenv\Dotenv, Exception;

// check environment
if (php_sapi_name() !== 'cli')
{
  echo 'Can not run in browser.';
  exit;
}

// load autoload
require __DIR__.'/../vendor/autoload.php';

// set static values
define('__API_GOOSE__', true);
define('__API_DEBUG__', true);
define('__API_PATH__', realpath(__DIR__.'/../'));

/**
 * load env
 * @throws Exception
 */
function loadEnv(): void
{
  try
  {
    $dotenv = Dotenv::createImmutable(__API_PATH__);
    $dotenv->load();
    if (count($_ENV) <= 0)
    {
      $dotenv = Dotenv::createMutable(__API_PATH__);
      $dotenv->load();
    }
  }
  catch(Exception $e)
  {
    throw new Exception('.env error');
  }
}

/**
 * get password
 * https://dasprids.de/blog/2008/08/22/getting-a-password-hidden-from-stdin-with-php-cli/
 */
function getPassword(bool $stars = false): string
{
  // Get current style
  $oldStyle = shell_exec('stty -g');

  if ($stars === false)
  {
    shell_exec('stty -echo');
    $password = rtrim(fgets(STDIN), "\n");
  }
  else
  {
    shell_exec('stty -icanon -echo min 1 time 0');
    $password = '';
    while (true)
    {
      $char = fgetc(STDIN);
      if ($char === "\n")
      {
        break;
      }
      else if (ord($char) === 127)
      {
        if (strlen($password) > 0)
        {
          fwrite(STDOUT, "\x08 \x08");
          $password = substr($password, 0, -1);
        }
      }
      else
      {
        fwrite(STDOUT, "*");
        $password .= $char;
      }
    }
  }

  // Reset old style
  shell_exec('stty ' . $oldStyle);

  // Return the password
  return $password;
}

/**
 * quiz (cli prompt)
 */
function quiz(string $prompt = '', bool $password = false): string
{
  if ($password)
  {
    fwrite(STDOUT, $prompt.': ');
    $password = getPassword(true);
    echo "\n";
    return $password;
  }
  else
  {
    echo $prompt.': ';
    return rtrim( fgets( STDIN ), "\n" );
  }
}

/**
 * make token
 */
function makeToken(): string
{
  try
  {
    // load env
    loadEnv();
    // set host
    $host = Util::getHost($_ENV['API_PATH_URL']);
    // check install
    Util::checkDirectories();
    // make public token
    $jwt = Token::make((object)[
      'time' => true,
      'exp' => false,
      'host' => $host,
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
 * reset password
 */
function resetPassword(): void
{
  try
  {
    // load env
    loadEnv();

    // print header message
    echo "\n***\n";
    echo "RESET USER PASSWORD\n";
    echo "Please be careful to use.\n";
    echo "***\n\n";

    // quiz - id
    $answer_email = quiz('Please input user `e-mail`');

    // set modal
    $model = new Model();
    $model->connect();

    // search user
    $user = $model->getCount((object)[
      'table' => 'users',
      'where' => "email='$answer_email'",
      'debug' => false
    ])->data;
    if ($user <= 0) throw new Exception('No users found.');

    // quiz - new password
    $answer_new_password = quiz('New password', true);
    $answer_confirm_password = quiz('Confirm password', true);

    if ($answer_new_password !== $answer_confirm_password)
    {
      throw new Exception('`New password` and `Confirm password` are different.');
    }

    // update password
    $update = $model->edit((object)[
      'table' => 'users',
      'where' => "email='$answer_email'",
      'data' => [
        "password='".Text::createPassword($answer_new_password)."'"
      ],
    ]);
    if (!$update->success)
    {
      throw new Exception('Failed update user password.');
    }

    // end
    echo "\nSuccess update password.";
  }
  catch(Exception $e)
  {
    print_r("\nERROR: ".$e->getMessage());
  }
}

// switching action
switch ($argv[1])
{
  case 'ready':
    Install::ready();
    break;

  case 'install':
    Install::install();
    break;

  case 'make-token':
    echo makeToken();
    echo "\n";
    break;

  case 'reset-password':
    resetPassword();
    echo "\n";
    break;

  default:
    echo "ERROR: no argv. `php tools.php {ready,install,make-token,reset-password}`\n";
    break;
}
