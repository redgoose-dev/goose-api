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
  private static function getModel(): Model
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
  public static function login(object $op): object
  {
    if (!$op) throw new Exception('There is no option.', 401);
    if (!(isset($op->email) || isset($op->user_srl))) throw new Exception('No user_srl or email.', 401);
    if (!isset($op->password)) throw new Exception('No password.', 401);
    if (!isset($op->model)) $op->model = self::getModel();

    // set where
    $where = '';
    if (isset($op->user_srl))
    {
      $where = 'srl="'.$op->user_srl.'"';
    }
    else if (isset($op->email))
    {
      $where = 'email="'.$op->email.'"';
    }

    // get user data
    $user = $op->model->getItem((object)[
      'table' => 'users',
      'where' => $where,
      'debug' => __API_DEBUG__,
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
   * @param string $checkUserType `user|admin`
   * @return object 토큰을 재발급 받는다면 리턴으로 나온 토큰주소와 토큰 데이터
   * @throws Exception
   */
  public static function checkAuthorization(Model $getModel, ?string $checkUserType): object
  {
    try
    {
      if (!__API_TOKEN__)
      {
        throw new Exception('Not found `Authorization` in header');
      }
      $jwt = Token::get(__API_TOKEN__);

      // API 모드에서 올바른 URL인지 검사한다.
      if (__API_MODE__ === 'api')
      {
        // check url
        try
        {
          if (!$jwt->url)
          {
            throw new Exception('no url in jwt');
          }
          if (preg_match('/^http/', $jwt->url))
          {
            if ($_ENV['API_PATH_URL'] !== $jwt->url)
            {
              throw new Exception('url error');
            }
          }
          else if (!preg_match('/'.preg_quote($jwt->url, '/').'$/', $_ENV['API_PATH_URL']))
          {
            throw new Exception('url error');
          }
        }
        catch(Exception $e)
        {
          throw new Exception('The tokens "API_PATH_URL" and "API_PATH_URL" are different.');
        }
      }

      // check token id
      if ($_ENV['API_TOKEN_ID'] !== $jwt->id)
      {
        throw new Exception('Error `API_TOKEN_ID`');
      }

      // check user type
      // 검사할 항목인데 `user`로 설정할때 로그인 토큰이 아니라면 오류를 내보낸다.
      // `null`이라면 제한없다.
      switch($checkUserType)
      {
        case 'user':
          if (!(isset($jwt->data->srl) && is_int($jwt->data->srl)))
          {
            throw new Exception('You are not a logged in user.');
          }
          break;
        case 'admin':
          if (!(isset($jwt->data->admin) && (int)$jwt->data->admin > 0))
          {
            throw new Exception('You can not access.');
          }
          break;
      }

      // set model
      $model = ($getModel && $getModel->db) ? $getModel : self::getModel();

      // check blacklist token
      $sign = explode('.', __API_TOKEN__)[2];
      $blacklistToken = $model->getCount((object)[
        'table' => 'tokens',
        'where' => "token LIKE '{$sign}'",
      ]);
      if (!$getModel) $model->disconnect();
      if ($blacklistToken->data)
      {
        throw new Exception('Blacklisted token');
      }

      return (object)[
        'jwt' => $jwt->token ?? null,
        'data' => $jwt->data ?? null,
      ];
    }
    catch(Exception $e)
    {
      throw new Exception('Authorization error : '.$e->getMessage(), 401);
    }
  }

}
