<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_popular
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_content/models', 'ContentModel');

/**
 * Helper for mod_articles_popular
 *
 * @since  1.6.0
 */
class ModJeSocialHelper
{
	/**
	 * Method to get article data.
	 *
	 * @param   integer  $pk  The id of the article.
	 *
	 * @return  object|boolean|JException  Menu item data object on success, boolean false or JException instance on error
	 */
	public static function getItem($pk = null)
	{

		$app = JFactory::getApplication('site');
		$params = $app->getParams();
		
		// Load state from the request.
		$pk = $app->input->getInt('id');
        // Get an instance of the generic articles model
        $model = JModelLegacy::getInstance('Article', 'ContentModel', array('ignore_request' => true));
        $model->setState('params', $params);
        $item = $model->getItem($pk);
        return $item;
	}
}
