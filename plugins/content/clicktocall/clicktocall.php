<?php
// DocBlock评注可以使自动化工具更加容易生成APIs的文档
// 也帮助IDEs提供代码完整性
/**
* ClickToCall Content Plugin
*
* @package Joomla.Plugin
* @subpackage Content.clicktocall
* @since 3.0
*/

// 给潜在的黑客尽可能透露越少信息
defined('_JEXEC') or die;

// 从核心库加载标准的Joomla！插件类
jimport('joomla.plugin.plugin');

class plgContentClicktocall extends JPlugin
{
	// 兼容php 4和5的构造函数
	function plgContentClicktocall(&$subject, $params)
	{
		parent::__construct($subject, $params);
	}

	// 当Joomla！准备好内容时，该事件将触发插件运行在内容显示前，使我们可以改变显示的内容
	// &$row为引用$row的地址
	public function onContentPrepare($context, &$row, &$params, $page = 0){
		// 如果content被索引，那么不运行插件
		if($context == 'com_finder.indexer')
		{
			return true;
		}
		if(is_object($row)){
			return $this->clickToCall($row->text, $params);
		}
		return $this->clickToCall($row, $params);
	}

	protected function clickToCall(&$text, &$params)
	{
		$phoneDigits1 = $this->params->get('phoneDigits1', 4);
		$phoneDigits2 = $this->params->get('phoneDigits2', 4);
		// 匹配4个数字后跟随一个连字符或空格,
		// 然后跟随4个数字。
		// 手机号码以如下形式: XXXX-XXXX or XXXX XXXX
		$pattern = '/(\W[0-9]{'.$phoneDigits1.'})-? ?(\W[0-9]{'.$phoneDigits2.'})/';
		$replacement = '<a href="tel:$1$2">$1$2</a>';
		$text = preg_replace($pattern, $replacement, $text);

		return true;
	}
}