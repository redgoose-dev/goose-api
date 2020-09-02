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
  Util::checkExistValue($this->post, [ 'email', 'password', 'host' ]);

  // connect db
  $this->model->connect();

  // check authorization
  Auth::checkAuthorization($this->model);

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
    'data' => (object)[
      'type' => 'user',
      'user_srl' => $user->srl,
      'email' => $user->email,
      'admin' => !!((int)$user->admin === 2),
      'host' => $this->post->host,
      'regdate' => date('Y-m-d H:i:s'),
    ],
  ]);

  // set data
  $data->srl = (int)$user->srl;
  $data->email = $user->email;
  $data->name = $user->name;
  $data->admin = !!((int)$user->admin === 2);
  $data->token = $jwt->token;
  $data->host = $this->post->host;

  // disconnect db
  $this->model->disconnect();

  // set output
  $output->code = 200;
  $output->data = $data;

  // output
  return Output::data($output);
}
catch(Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::data($e->getMessage(), $e->getCode());
}
