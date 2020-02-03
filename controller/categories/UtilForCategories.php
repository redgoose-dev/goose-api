<?php
namespace Controller\Categories;
use Core;

/**
 * util for categories
 */

class UtilForCategories {

	/**
	 * extend article count
	 *
	 * @param Core\Model $model
	 * @param object $token
	 * @param array $index
	 * @return array
	 */
	public static function extendArticleCountInItems($model, $token, $index)
	{
		foreach ($index as $k=>$v)
		{
			$where = 'category_srl='.(int)$v->srl;
			$where .= (!$token->data->admin && $token->data->user_srl) ? ' and user_srl='.(int)$token->data->user_srl : '';
			$cnt = $model->getCount((object)[
				'table' => 'articles',
				'where' => $where,
			]);
			$index[$k]->count_article = $cnt->data;
		}

		return $index;
	}

	/**
	 * extend all item
	 *
	 * @param Core\Model $model
	 * @param object $token
	 * @param array $index
	 * @param int $nest_srl
	 *
	 * @return array
	 */
	public static function extendAllArticlesInItems($model, $token, $index, $nest_srl)
	{
		// set item
		$item = (object)[
			'srl' => '',
			'nest_srl' => $nest_srl,
			'name' => 'All',
		];

		// get article count
		if (Core\Util::checkKeyInExtField('count_article'))
		{
			$where = $nest_srl ? 'nest_srl='.$nest_srl : '';
			$where .= (!$token->data->admin && $token->data->user_srl) ? ' and user_srl='.(int)$token->data->user_srl : '';
			$cnt = $model->getCount((object)[
				'table' => 'articles',
				'where' => $where,
			]);
			$item->count_article = $cnt->data;
		}

		// add item
		array_unshift($index, $item);

		return $index;
	}

	/**
	 * extend none item
	 *
	 * @param Core\Model $model
	 * @param object $token
	 * @param array $index
	 * @param int $nest_srl
	 *
	 * @return array
	 */
	public static function extendNoneArticleInItems($model, $token, $index, $nest_srl)
	{
		// set item
		$item = (object)[
			'srl' => 'null',
			'nest_srl' => $nest_srl,
			'name' => 'none',
		];
		if (Core\Util::checkKeyInExtField('count_article'))
		{
			$where = $nest_srl ? 'nest_srl='.$nest_srl : '';
			$where .= (!$token->data->admin && $token->data->user_srl) ? ' and user_srl='.(int)$token->data->user_srl : '';
			$where .= ' and category_srl IS NULL';
			$cnt = $model->getCount((object)[
				'table' => 'articles',
				'where' => $where,
			]);
			$item->count_article = $cnt->data;
		}
		// add item
		array_push($index, $item);

		return $index;
	}

	/**
	 * extend item
	 * 목록에 대한 확장기능
	 *
	 * @param Core\Model $model
	 * @param object $token
	 * @param array $index
	 * @param int $nest_srl
	 *
	 * @return array
	 */
	public static function extendItems($model, $token, $index, $nest_srl)
	{
		if (!(isset($index) && count($index))) return [];

		// get article count
		if (Core\Util::checkKeyInExtField('count_article'))
		{
			$index = self::extendArticleCountInItems($model, $token, $index);
		}
		// get all item
		if (Core\Util::checkKeyInExtField('item_all'))
		{
			$index = self::extendAllArticlesInItems($model, $token, $index, $nest_srl);
		}
		// get none category
		if (Core\Util::checkKeyInExtField('none'))
		{
			$index = self::extendNoneArticleInItems($model, $token, $index, $nest_srl);
		}

		return $index;
	}

}
