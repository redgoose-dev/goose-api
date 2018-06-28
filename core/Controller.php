<?php
namespace Core;
use Exception;


class Controller {

	/**
	 * result index
	 *
	 * @param Goose $goose
	 * @param string $table
	 * @param string $where
	 * @param array $jsonField
	 */
	public static function index($goose, $table=null, $where=null, $jsonField=['json'])
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
			if (!$goose || !$table) throw new Exception('', 500);

			// get values
			$output = (object)[];
			$model = new Model();
			$page = (($_GET['page']) ? (int)$_GET['page'] : $goose->defaults->page) - 1;
			$size = ($_GET['size']) ? (int)$_GET['size'] : $goose->defaults->size;

			// connect db
			$tmp = $model->connect();
			if ($tmp)
			{
				throw new Exception($tmp->getMessage(), $tmp->getCode());
			}

			// get total
			$total = $model->getCount((object)[
				'table' => $table,
				'where' => $where,
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
				$limit = [ $page * $size, $size ];
			}

			// get datas
			$items = $model->getItems((object)[
				'table' => $table,
				'field' => $_GET['field'],
				'json_field' => $jsonField,
				'order' => $_GET['order'],
				'sort' => $_GET['sort'],
				'limit' => $limit,
				'where' => $where,
				'debug' => __DEBUG__
			]);

			// disconnect db
			$model->disconnect();

			// set output
			$output->code = 200;
			$output->url = $_SERVER['PATH_URL'].$_SERVER['REQUEST_URI'];
			$output->query = $items->query;
			if (isset($total->data)) $output->total = $total->data;
			$output->data = $items->data;

			// output data
			Output::json($output);
		}
		catch (Exception $e)
		{
			Output::json((object)[
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			]);
		}
	}

	/**
	 * result item
	 *
	 * @param Goose $goose
	 * @param string $table
	 * @param int $srl
	 */
	public static function item($goose, $table=null, $srl=null)
	{

	}

}