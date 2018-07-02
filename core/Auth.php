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
		if (!$op) throw new Exception('There is no option.');
		if (!$op->email) throw new Exception('No email');
		if (!$op->password) throw new Exception('No password');
		if (!$op->model) $op->model = self::getModel();

		// get user data
		$user = $op->model->getItem((object)[
			'table' => 'user',
			'where' => 'email="'.$op->email.'"',
			'debug' => __DEBUG__,
		]);
		if (!$user->data) throw new Exception('no user');

		// check password
		if (!password_verify($op->password, $user->data->pw)) throw new Exception('Error password');

		return $user->data;
	}

	public static function logout()
	{

	}

	/**
	 * check authorization
	 *
	 * @param int $level
	 * @param boolean $backdoor
	 * @return string 토큰을 재발급 받는다면 리턴으로 나온 토큰주소
	 * @throws Exception
	 */
	public static function checkAuthorization($level=0, $backdoor=false)
	{
		try
		{
			$jwt = Token::get($_SERVER['HTTP_AUTHORIZATION']);
			$jwt_level = $jwt->data->level ? $jwt->data->level : 0;
			if (!$backdoor)
			{
				if (getenv('PATH_URL') !== $jwt->url)
				{
					throw new Exception('error');
				}
				if (getenv('TOKEN_ID') !== $jwt->jti)
				{
					throw new Exception('error');
				}
				if ($level > $jwt_level)
				{
					throw new Exception('error');
				}
			}
			return $jwt->token;
		}
		catch(Exception $e)
		{
			throw new Exception('Authorization error', 401);
		}
	}

}