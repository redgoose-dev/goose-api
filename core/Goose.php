<?php
namespace Core;
use Exception;


/**
 * Goose
 * 인스톨이 된 상태에서 진행되는 행동에 관한 클래스.
 * - 설정파일 불러오기
 * - 토큰 검사
 * - 라우터 초기화
 * - url 라우트에 의한 컨트롤러 실행
 *
 * @property string path
 * @property object config
 * @property Router router
 * @property string target
 * @property array params
 */

class Goose {

	public function __construct()
	{
		$this->path = null;
		$this->config = null;
		$this->router = new Router();
		$this->target = null;
		$this->params = null;
	}

	/**
	 * routing to controller
	 * 라우팅에 의한 해석된 값으로 컨트롤러로 넘겨주는 과정
	 *
	 * @throws \Exception
	 */
	private function turningPoint()
	{
		try
		{
			// check $target
			if (!$this->target) throw new Exception('Not found target', 404);

			// search controller
			if (file_exists($this->path.'/controller/'.$this->target.'.php'))
			{
				require $this->path.'/controller/'.$this->target.'.php';
			}
		}
		catch(Exception $e)
		{
			$this->error($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * error
	 * 오류 컨트롤러 실행
	 *
	 * @param string $message
	 * @param int $code
	 */
	private function error($message='Service error', $code=500)
	{
		// TODO: 오류 클래스로 바꿔볼까 고민됨. static으로 바로 실행할 수 있도록...
		require $this->path.'/controller/error.php';
	}

	/**
	 * Play app trigger
	 *
	 * @param string $path
	 * @throws Exception
	 */
	public function run($path='')
	{
		// check $path
		if (!$path)
		{
			return $this->error('Not found $path', 500);
		}

		// check install
		if (!file_exists(__DIR__.'/../data/config.php'))
		{
			return $this->error('Not found config', 500);
		}

		// set path
		$this->path = $path;

		// set config
		$this->config = require __DIR__.'/../data/config.php';

		// TODO: checking token

		// initialize routing
		$this->router->init();

		// check router match
		if (!$this->router->match)
		{
			return $this->error('Not found match', 500);
		}

		// set router values
		$this->target = $this->router->match['target'];
		$this->params = $this->router->match['params'];

		// run turning point
		$this->turningPoint();
	}

}