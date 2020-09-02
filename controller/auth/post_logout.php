<?php
namespace Core;
use Exception;

if (!defined('__API_GOOSE__')) exit();

/**
 * post logout
 * 주 목적은 만료되지도 않은 토큰 사용을 막기위하여 블랙리스트용 토큰을 추가하기 위함.
 * 유저 로그인이라면 블랙리스트에 시그니쳐값을 추가하고 익명 토큰을 만들어서 결과값으로 출력한다.
 *
 * @var Goose|Connect $this
 */

try
{
  // set values
  $output = (object)[];
  $sign = explode('.', __API_TOKEN__)[2];

  // get decode token
  $jwt = Token::get(__API_TOKEN__);

  // if user token
  if ($jwt->data->type !== 'user')
  {
    throw new Exception(Message::make('msg.notUserToken'));
  }
  if (!$jwt->exp)
  {
    throw new Exception(Message::make('msg.tokenExpired'));
  }

  // connect db
  $this->model->connect();

  // check blacklist token
  $blacklistToken = $this->model->getCount((object)[
    'table' => 'tokens',
    'where' => 'token LIKE \''.$sign.'\'',
  ]);
  if ($blacklistToken->data)
  {
    throw new Exception(Message::make('msg.blacklistTokens'));
  }

  // add token to blacklist
  $this->model->add((object)[
    'table' => 'tokens',
    'data' => (object)[
      'srl' => null,
      'token' => $sign,
      'expired' => $jwt->exp,
      'regdate' => date('Y-m-d H:i:s'),
    ],
  ]);

  // make new public token
  $newToken = Token::make((object)[
    'time' => true,
    'exp' => false,
  ]);

  // set output
  $output->code = 200;
  $output->token = $newToken->token;

  // disconnect db
  $this->model->disconnect();

  // output
  return Output::data($output);
}
catch(Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::data($e->getMessage(), $e->getCode());
}
