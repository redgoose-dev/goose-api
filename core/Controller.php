<?php
namespace Core;
use Exception;


class Controller {

	/**
	 * connect db
	 *
	 * @param Model $model
	 * @return Model
	 * @throws Exception
	 */
	public static function connect($model)
	{
		if (!!$model)
		{
			return $model;
		}
		else
		{
			$model = new Model();
			$model->connect();
			return $model;
		}
	}

	/**
	 * disconnect db
	 *
	 * @param Model $model
	 * @param boolean $sw make user model
	 */
	public static function disconnect($model, $sw=false)
	{
		// 컨트롤러 클래스에서 만든 모델이라면 disconnect() 실행
		if (!$sw) $model->disconnect();
	}

	/**
	 * index
	 *
	 * @param object $op
	 * @param callable $callback
	 * @return object
	 * @throws Exception
	 */
	public static function index($op=null, callable $callback=null)
	{
		/**
		 * # $op guide
		 * @param Goose $op->goose
		 * @param Model $op->model
		 * @param string $op->table
		 * @param string $op->where
		 * @param array $op->json_field
		 *
		 * # url params guide
		 * @param string field
		 * @param string order
		 * @param string sort
		 * @param string limit
		 */

		if (!$op->goose || !$op->table)
		{
			throw new Exception('no object in Controller::index()', 500);
		}

		// get values
		$output = (object)[];
		$page = ($_GET['page']) ? (int)$_GET['page'] : 1;
		$size = ($_GET['size']) ? (int)$_GET['size'] : getenv('DEFAULT_INDEX_SIZE');

		// set model
		$model = self::connect($op->model);

		// get total
		$total = $model->getCount((object)[
			'table' => $op->table,
			'where' => $op->where,
			'debug' => __DEBUG__
		]);

		// set limit
		$limit = null;
		if (isset($_GET['unlimit']))
		{
			$limit = '';
		}
		else if (isset($_GET['limit']))
		{
			$limit = explode(',', $_GET['limit']);
		}
		else if (isset($_GET['page']) || isset($_GET['size']))
		{
			$limit = [ ($page - 1) * $size, $size ];
		}
		else
		{
			$limit = (int)getenv('DEFAULT_INDEX_SIZE');
		}

		// get datas
		$items = $model->getItems((object)[
			'table' => $op->table,
			'field' => $_GET['field'],
			'json_field' => $op->json_field,
			'order' => $_GET['order'],
			'sort' => $_GET['sort'],
			'limit' => $limit,
			'where' => $op->where,
			'debug' => __DEBUG__
		]);
		// 필요하면 산출된 데이터를 조정하기 위하여 콜백으로 한번 보낸다.
		if (is_callable($callback))
		{
			$items = $callback($items);
		}

		// disconnect db
		self::disconnect($model, $op->model);

		// set output
		$output->code = $total->data ? 200 : 404;
		$output->query = $items->query;
		if ($total->data)
		{
			$output->data = (object)[
				'total' => $total->data,
				'index' => $items->data,
			];
		}

		return $output;
	}

	/**
	 * item
	 *
	 * @param object $op
	 * @param callable $callback
	 * @return object
	 * @throws Exception
	 */
	public static function item($op=null, callable $callback=null)
	{
		/**
		 * # $op guide
		 * @param Goose $op->goose
		 * @param Model $op->model
		 * @param string $op->table
		 * @param int $op->srl
		 * @param array $op->json_field
		 * @param string $op->where
		 *
		 * # url params guide
		 * @param string field
		 */

		if (!$op->goose || !$op->table || !$op->srl)
		{
			throw new Exception('no object in Controller::item()', 500);
		}

		// get values
		$output = (object)[];

		// set model
		$model = self::connect($op->model);

		// get data
		$item = $model->getItem((object)[
			'table' => $op->table,
			'field' => $_GET['field'],
			'json_field' => $op->json_field,
			'where' => 'srl='.(int)$op->srl.$op->where,
			'debug' => __DEBUG__,
		]);
		// 필요하면 산출된 데이터를 조정하기 위하여 콜백으로 한번 보낸다.
		if (is_callable($callback))
		{
			$item = $callback($item);
		}

		// disconnect db
		self::disconnect($model, $op->model);

		// set output
		$output->code = $item->data ? 200 : 404;
		$output->query = $item->query;
		if ($item->data) $output->data = $item->data;

		return $output;
	}

	/**
	 * add
	 *
	 * @param object $op
	 * @return object
	 * @throws Exception
	 */
	public static function add($op=null)
	{
		if (!$op->goose || !$op->table || !$op->data)
		{
			throw new Exception('no object in Controller::item()', 500);
		}

		// get values
		$output = (object)[];

		// set model
		$model = self::connect($op->model);

		// add data
		$result = $model->add((object)[
			'table' => $op->table,
			'data' => $op->data,
			'debug' => __DEBUG__
		]);

		// set output
		$output->code = 200;
		$output->query = $result->query;
		$output->srl = $model->getLastIndex();

		// disconnect db
		self::disconnect($model, $op->model);

		return $output;
	}

	/**
	 * edit
	 *
	 * @param object $op
	 * @return object
	 * @throws Exception
	 */
	public static function edit($op=null)
	{
		/**
		 * # $op guide
		 * @param Goose $op->goose
		 * @param Model $op->model
		 * @param string $op->table
		 * @param int $op->srl
		 * @param array $op->data
		 */

		if (!$op->goose || !$op->table || !$op->srl || !$op->data)
		{
			throw new Exception('no object in Controller::item()', 500);
		}

		// get values
		$output = (object)[];

		// set model
		$model = self::connect($op->model);

		// update data
		$result = $model->edit((object)[
			'table' => $op->table,
			'where' => 'srl='.(int)$op->srl,
			'data' => $op->data,
			'debug' => __DEBUG__
		]);

		// disconnect db
		self::disconnect($model, $op->model);

		// set output
		$output->code = 200;
		$output->query = $result->query;

		return $output;
	}

	/**
	 * delete
	 *
	 * @param object $op
	 * @return object
	 * @throws Exception
	 */
	public static function delete($op=null)
	{
		/**
		 * # $op guide
		 * @param Goose $op->goose
		 * @param Model $op->model
		 * @param string $op->table
		 * @param int $op->srl
		 */

		if (!$op->goose || !$op->table || !$op->srl)
		{
			throw new Exception('no object in Controller::delete()', 500);
		}

		// get values
		$output = (object)[];

		// set model
		$model = self::connect($op->model);

		// delete data
		$result = $model->delete((object)[
			'table' => $op->table,
			'where' => 'srl='.(int)$op->srl,
			'debug' => __DEBUG__,
		]);

		// disconnect db
		self::disconnect($model, $op->model);

		// set output
		$output->code = 200;
		$output->query = $result->query;

		return $output;
	}

}