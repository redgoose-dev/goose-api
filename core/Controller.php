<?php
namespace Core;
use Exception;


class Controller {

	/**
	 * result index
	 *
	 * @param object $op
	 * @param Goose $op->goose
	 * @param Model $op->model
	 * @param boolean $op->auth
	 * @param string $op->table
	 * @param string $op->where
	 * @param array $op->jsonField
	 * @param function $callback
	 *
	 * @throws Exception
	 */
	public static function index($op=null, callable $callback=null)
	{
		/**
		 * url params guide
		 *
		 * @param string field
		 * @param string order
		 * @param string sort
		 * @param string limit
		 * @param int page
		 * @param int size
		 */

		if (!$op->goose || !$op->table) throw new Exception('', 500);

		// get values
		$output = (object)[];
		$page = ($_GET['page']) ? (int)$_GET['page'] : $op->page;
		$size = ($_GET['size']) ? (int)$_GET['size'] : $op->size;

		// set model
		if (!$op->model)
		{
			$op->model = new Model();
			// connect db
			if ($tmp = $op->model->connect())
			{
				throw new Exception($tmp->getMessage(), $tmp->getCode());
			}
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
		$output->url = $_SERVER['PATH_URL'].$_SERVER['REQUEST_URI'];
		$output->query = $items->query;
		if (isset($total->data)) $output->total = $total->data;
		$output->data = $items->data;

		return $output;
	}

	/**
	 * result item
	 *
	 * @param object $op
	 */
	public static function item($op=null)
	{

	}

}