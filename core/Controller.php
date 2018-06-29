<?php
namespace Core;
use Exception;


class Controller {

	/**
	 * result index
	 *
	 * @param object $op
	 * @param Goose $op->goose
	 * @param string $op->table
	 * @param string $op->where
	 * @param array $op->jsonField
	 * @param function $callback
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
		try
		{
			if (!$op->goose || !$op->table) throw new Exception('', 500);

			// get values
			$output = (object)[];
			$model = new Model();
			$page = ($_GET['page']) ? (int)$_GET['page'] : $op->page;
			$size = ($_GET['size']) ? (int)$_GET['size'] : $op->size;

			// connect db
			$tmp = $model->connect();
			if ($tmp)
			{
				throw new Exception($tmp->getMessage(), $tmp->getCode());
			}

			// get total
			$total = $model->getCount((object)[
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
			$items = $model->getItems((object)[
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
			$model->disconnect();

			// set output
			$output->code = 200;
			$output->url = $_SERVER['PATH_URL'].$_SERVER['REQUEST_URI'];
			$output->query = $items->query;
			if (isset($total->data)) $output->total = $total->data;
			$output->data = $items->data;

			// output data
			Output::data($output);
		}
		catch (Exception $e)
		{
			Output::data((object)[
				'message' => ($e->getMessage()) ? $e->getMessage() : 'Unknown error',
				'code' => $e->getCode(),
			]);
		}
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