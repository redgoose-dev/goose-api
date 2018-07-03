<?php
namespace Core;
use Exception;


class Controller {

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
		 * @param array $op->jsonField
		 *
		 * # url params guide
		 * @param string field
		 * @param string order
		 * @param string sort
		 * @param string limit
		 * @param int page
		 * @param int size
		 */

		if (!$op->goose || !$op->table)
		{
			throw new Exception('no object in Controller::index()', 500);
		}

		// get values
		$output = (object)[];
		$page = ($_GET['page']) ? (int)$_GET['page'] : $op->page;
		$size = ($_GET['size']) ? (int)$_GET['size'] : $op->size;

		// set model
		if (!$op->model)
		{
			$op->model = new Model();
			$op->model->connect();
		}

		// get total
		$total = $op->model->getCount((object)[
			'table' => $op->table,
			'where' => $op->where,
			'debug' => __DEBUG__
		]);

		// set limit
		$limit = null;
		if (isset($_GET['limit']))
		{
			$limit = explode(',', $_GET['limit']);
		}
		else if (isset($_GET['page']) || isset($_GET['size']))
		{
			$limit = [ ($page - 1) * $size, $size ];
		}

		// get datas
		$items = $op->model->getItems((object)[
			'table' => $op->table,
			'field' => $_GET['field'],
			'json_field' => $op->jsonField,
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
		$op->model->disconnect();

		// set output
		$output->code = 200;
		$output->query = $items->query;
		if (isset($total->data)) $output->total = $total->data;
		$output->data = $items->data;

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
		 * @param array $op->jsonField
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
		if (!$op->model)
		{
			$op->model = new Model();
			$op->model->connect();
		}

		// get data
		$item = $op->model->getItem((object)[
			'table' => $op->table,
			'field' => $_GET['field'],
			'json_field' => $op->jsonField,
			'where' => 'srl='.(int)$op->srl,
			'debug' => __DEBUG__,
		]);
		// 필요하면 산출된 데이터를 조정하기 위하여 콜백으로 한번 보낸다.
		if (is_callable($callback))
		{
			$item = $callback($item);
		}

		// disconnect db
		$op->model->disconnect();

		// set output
		$output->code = 200;
		$output->query = $item->query;
		$output->data = $item->data;

		return $output;
	}

	/**
	 * item
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
		if (!$op->model)
		{
			$op->model = new Model();
			$op->model->connect();
		}

		// update data
		$result = $op->model->edit((object)[
			'table' => 'app',
			'where' => 'srl='.(int)$op->srl,
			'data' => $op->data,
			'debug' => __DEBUG__
		]);

		// disconnect db
		$op->model->disconnect();

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
		if (!$op->model)
		{
			$op->model = new Model();
			$op->model->connect();
		}

		// delete data
		$result = $op->model->delete((object)[
			'table' => $op->table,
			'where' => 'srl='.(int)$op->srl,
			'debug' => __DEBUG__,
		]);

		// disconnect db
		$op->model->disconnect();

		// set output
		$output->code = 200;
		$output->query = $result->query;

		return $output;
	}

}