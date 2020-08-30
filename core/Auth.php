<?php
namespace Core;
use Exception;


class Auth {

  /**
   * get model
   *
   * @return Model
   * @throws Exception
   */
  private static function getModel()
  {
    try
    {
      $model = new Model();
      $model->connect();
      return $model;
    }
    catch(Exception $e)
    {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * login
   *
   * @param object $op
   * @return object
   * @throws Exception
   */
  public static function login($op=null)
  {
    if (!$op) throw new Exception('There is no option.', 401);
    if (!$op->email && !$op->user_srl) throw new Exception('No user_srl or email.', 401);
    if (!$op->password) throw new Exception('No password.', 401);
    if (!$op->model) $op->model = self::getModel();

    // set where
    $where= null;
    if ($op->user_srl)
    {
      $where = 'srl="'.$op->user_srl.'"';
    }
    else if ($op->email)
    {
      $where = 'email="'.$op->email.'"';
    }

    // get user data
    $user = $op->model->getItem((object)[
      'table' => 'users',
      'where' => $where,
      'debug' => __DEBUG__,
    ]);
    if (!$user->data) throw new Exception('No user in database', 401);

    // check password
    if (!password_verify($op->password, $user->data->password)) throw new Exception('Error verify password', 401);

    return $user->data;
  }

  /**
   * check authorization
   *
   * @param Model $getModel
   * @param String $checkUserType `null|user|admin`
   * @return object 토큰을 재발급 받는다면 리턴으로 나온 토큰주소와 토큰 데이터
   * @throws Exception
   */
  public static function checkAuthorization($getModel=null, $checkUserType=null)
  {
    try
    {
      if (!__TOKEN__)
      {
        throw new Exception('Not found `Authorization` in header');
      }
      $jwt = Token::get(__TOKEN__);

      // check url
      try
      {
        if (!$jwt->url)
        {
          throw new Exception('error');
        }
        if (preg_match('/^http/', $jwt->url))
        {
          if ($_ENV['API_PATH_URL'] !== $jwt->url)
          {
            throw new Exception('error');
          }
        }
        else if (!preg_match('/'.preg_quote($jwt->url, '/').'$/', $_ENV['API_PATH_URL']))
        {
          throw new Exception('error');
        }
      }
      catch(Exception $e)
      {
        throw new Exception('The tokens "API_PATH_URL" and "API_PATH_URL" are different.');
      }

      // check token id
      if ($_ENV['API_TOKEN_ID'] !== $jwt->token_id)
      {
        throw new Exception('Not found `API_TOKEN_ID`');
      }

      // check user type
      // 검사할 항목인데 `user`로 설정할때 로그인 토큰이 아니라면 오류를 내보낸다.
      // `null`이라면 제한없다.
      switch($checkUserType)
      {
        case 'user':
          if ($jwt->data->type !== 'user')
          {
            throw new Exception('You are not a logged in user.');
          }
          break;
        case 'admin':
          if (!$jwt->data->admin)
          {
            throw new Exception('You can not access.');
          }
          break;
      }

      // set model
      $model = ($getModel) ? $getModel : self::getModel();

      // check blacklist token
      $sign = explode('.', __TOKEN__)[2];
      $blacklistToken = $model->getCount((object)[
        'table' => 'tokens',
        'where' => 'token LIKE \''.$sign.'\''
      ]);
      if (!$getModel) $model->disconnect();
      if ($blacklistToken->data)
      {
        throw new Exception('Blacklisted token');
      }

      return (object)[
        'jwt' => $jwt->token,
        'data' => $jwt->data
      ];
    }
    catch(Exception $e)
    {
      throw new Exception('Authorization error : '.$e->getMessage(), 401);
    }
  }

}
