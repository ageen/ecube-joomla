<?php
defined('_JEXEC') or die;

// 抽象类不能被实例化
abstract class ModLatestextensionsHelper
{
	// 用于查询和加载数据
	public static function getList(&$params)
	{
		// 建立一个数据库连接
		$db = JFactory::getDbo();
		// true参数声明使用新的查询query，不是继续前一个查询
		$query = $db->getQuery(true);

		// 准备查询语句
		// Joomla!支持未知数据库使用，查询会忽略使用何种数据库
		$query->select('name, extension_id, type');
		// #__数据库前缀
		$query->from('#__extensions');
		$query->order('extension_id DESC');

		// 设置查询语句
		$db->setQuery($query, 0, $params->get('count', 5));

		// try{}catch{}语句块
		try
		{
			// 获取结果集，多结果数组使用loadObjectList
			$results = $db->loadObjectList();
		}
		catch(RuntimeException $e)
		{
			JError::raiseError(500, $e->getMessage());
			return false;
		}

		// 循环处理结果集内容，过滤内容中的代码
		foreach ($results as $k => $result) {
			$results[$k] = new stdClass;
			$results[$k]->name = htmlspecialchars($result->name);
			$results[$k]->id = (int)$result->extension_id;
			$results[$k]->type = htmlspecialchars($result->type);
		}

		return $results;
	}
}