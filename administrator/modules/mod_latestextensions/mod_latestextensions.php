<?php
defined('_JEXEC') or die;

// 加载帮助文件，注意避免使用require_once
JLoader::register('ModLatestExtensionsHelper', __DIR__ . '/helper.php');

// 通过helper类加载显示数据
$list = ModLatestExtensionsHelper::getList($params);
// 加载自定义模块类后缀
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
// 加载该模块的css样式文件
JHTML::_('stylesheet', 'mod_latestextensions/style.css', array(), true);
// 在我们的视图中，加载默认布局文件/tmpl/default.php
require JModuleHelper::getLayoutPath('mod_latestextensions', $params->get('layout', 'default'));