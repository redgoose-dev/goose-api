<?php
namespace Core;
use Exception;


class Install {

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

	public function run()
	{
		echo "install form";
	}

}