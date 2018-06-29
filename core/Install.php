<?php
namespace Core;


class Install {

	/**
	 * Check installed
	 *
	 * @return boolean
	 */
	static public function check()
	{
		// check `/data`
		if (!is_dir(__PATH__.'/data')) return false;
		if (!is_writable(__PATH__.'/data')) return false;

		// check `/data/upload`
		if (!is_dir(__PATH__.'/data/upload')) return false;
		if (!is_writable(__PATH__.'/data/upload')) return false;

		// check `/data/settings`
		if (!is_dir(__PATH__.'/data/settings')) return false;
		if (!is_writable(__PATH__.'/data/settings')) return false;

		// check env
		// TODO: .env 스펙이 확정되면 검사하기
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

		return true;
	}

	public function run()
	{
		echo "install form";
	}

}