<?php
namespace Controller\articles;
use Exception;

/**
 * util for articles
 */

class Util {

	/**
	 * get next page number
	 *
	 * @param \Core\Goose $goose
	 * @param \Core\Model $model
	 * @param string $where
	 * @return int
	 * @throws
	 */
	public static function getNextPage($goose, $model, $where)
	{
		try
		{
			if (!$_GET['page']) $_GET['page'] = 1;
			$_GET['page'] = (int)$_GET['page'] + 1;
			$_GET['field'] = 'srl';
			$next_output = \Core\Controller::index((object)[
				'goose' => $goose,
				'model' => $model,
				'table' => 'articles',
				'field' => 'srl',
				'where' => $where,
			]);
			if ($next_output->data && $next_output->data->index && count($next_output->data->index))
			{
				return (int)$_GET['page'];
			}
			return null;
		}
		catch(Exception $e)
		{
			return null;
		}
	}

	/**
	 * extend category name in items
	 *
	 * @param \Core\Model $model
	 * @param array $index
	 * @return array
	 */
	public static function extendCategoryNameInItems($model, $index)
	{
		if (!(isset($index) && count($index))) return [];

		foreach ($index as $k=>$v)
		{
			if (!$v->category_srl) continue;
			$category = $model->getItem((object)[
				'table' => 'categories',
				'field' => 'name',
				'where' => 'srl='.(int)$v->category_srl,
			]);
			if ($category->data && $category->data->name)
			{
				$index[$k]->category_name = $category->data->name;
			}
		}

		return $index;
	}

	/**
	 * extend nest name in items
	 *
	 * @param \Core\Model $model
	 * @param array $index
	 * @return array
	 */
	public static function extendNestNameInItems($model, $index)
	{
		if (!(isset($index) && count($index))) return [];

		foreach ($index as $k=>$v)
		{
			if (!$v->nest_srl) continue;
			$nest = $model->getItem((object)[
				'table' => 'nests',
				'field' => 'name',
				'where' => 'srl='.(int)$v->nest_srl,
			]);
			if ($nest->data && $nest->data->name)
			{
				$index[$k]->nest_name = $nest->data->name;
			}
		}

		return $index;
	}

}