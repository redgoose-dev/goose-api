<?php
namespace Core;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * post login
 * 이메일과 패스워드로 로그인 검사를 하고, 사용자 정보와 새로 만들어진 유저용 토큰을 출력한다.
 *
 * @var Goose|Connect $this
 */

try
{
  // check post values
  Util::checkExistValue($this->post, [ 'email', 'password' ]);

  // connect db
  $this->model->connect();

  // check authorization
  Auth::checkAuthorization($this->model, '', false);

  // set values
  $output = (object)[];
  $data = (object)[];

  // get user data
  $user = Auth::login((object)[
    'model' => $this->model,
    'email' => $this->post->email,
    'password' => $this->post->password,
  ]);

  // make token
  $jwt = Token::make((object)[
    'exp' => true,
    'time' => true,
    'host' => apache_request_headers()['Host'],
    'data' => (object)[
      'srl' => $user->srl,
      'admin' => !!((int)$user->admin === 1),
    ],
  ]);

  // set data
  $data->srl = (int)$user->srl;
  $data->email = $user->email;
  $data->name = $user->name;
  $data->admin = !!((int)$user->admin === 1);
  $data->token = $jwt->token;

  // disconnect db
  $this->model->disconnect();

  // set output
  $output->code = 200;
  $output->data = $data;

  // output
  return Output::result($output);
}
catch(Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  Error::result($e->getMessage(), $e->getCode());
}
