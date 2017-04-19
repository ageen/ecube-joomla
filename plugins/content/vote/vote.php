<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.vote
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Vote plugin.
 *
 * @since  1.5
 */
class PlgContentVote extends JPlugin
{
	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 * @since  3.6
	 */
	protected $app;
	/**
	 * The position the voting data is displayed in relative to the article.
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $votingPosition;

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->votingPosition = $this->params->get('position', 'top');
	}

	/**
	 * Displays the voting area if in an article
	 *
	 * @param   string   $context  The context of the content being passed to the plugin
	 * @param   object   &$row     The article object
	 * @param   object   &$params  The article params
	 * @param   integer  $page     The 'page' number
	 *
	 * @return  mixed  html string containing code for the votes if in com_content else boolean false
	 *
	 * @since   1.6
	 */
	public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
	{
		if ($this->votingPosition !== 'top')
		{
			return '';
		}
		return $this->displayVotingData($context, $row, $params, $page);
	}

	public function onContentAfterDisplay($context, &$row, &$params, $page = 0)
	{
		if ($this->votingPosition !== 'bottom')
		{
			return '';
		}
		return $this->displayVotingData($context, $row, $params, $page);
	}

	public function displayVotingData($context, &$row, &$params, $page)
	{
		$parts = explode(".", $context);

		if ($parts[0] != 'com_content')
		{
			return false;
		}

		$html = '';

		if (!empty($params) && $params->get('show_vote', null))
		{
			// Load plugin language files only when needed (ex: they are not needed if show_vote is not active).
			$this->loadLanguage();

			$rating = (int) @$row->rating;

			$view = JFactory::getApplication()->input->getString('view', '');
			$img = '';

			// Look for images in template if available
			$starImageOn  = JHtml::_('image', 'system/rating_star.png', JText::_('PLG_VOTE_STAR_ACTIVE'), null, true);
			$starImageOff = JHtml::_('image', 'system/rating_star_blank.png', JText::_('PLG_VOTE_STAR_INACTIVE'), null, true);

			for ($i = 0; $i < $rating; $i++)
			{
				$img .= $starImageOn;
			}

			for ($i = $rating; $i < 5; $i++)
			{
				$img .= $starImageOff;
			}

			$html .= '<div class="content_rating" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">';
			$html .= '<p class="unseen element-invisible">'
					. JText::sprintf('PLG_VOTE_USER_RATING', '<span itemprop="ratingValue">' . $rating . '</span>', '<span itemprop="bestRating">5</span>')
					. '<meta itemprop="ratingCount" content="' . (int) $row->rating_count . '" />'
					. '<meta itemprop="worstRating" content="0" />'
					. '</p>';
			$html .= '<div class="rate-star">' . $img . '</div>';
			$html .= '</div>';

			if ($view == 'article' && $row->state == 1)
			{
				$uri = clone JUri::getInstance();
				$uri->setVar('hitcount', '0');

				// Create option list for voting select box
				$options = array();

				for ($i = 1; $i < 6; $i++)
				{
					$options[] = JHtml::_('select.option', $i, JText::sprintf('PLG_VOTE_VOTE', $i));
				}

				// Generate voting form
				$html .= '<div class="rate-form text-center"><form method="post" action="' . htmlspecialchars($uri->toString(), ENT_COMPAT, 'UTF-8') . '" class="form-inline">';
				$html .= '<div class="form-group">';
				$html .= '<label for="content_vote_' . $row->id . '">' . JText::_('PLG_VOTE_LABEL') . '&nbsp;</label>';
				$html .= JHtml::_('select.genericlist', $options, 'user_rating', 'class="form-control"', 'value', 'text', '5', 'content_vote_' . $row->id);
				$html .= '</div>';
				$html .= '&#160;<input class="btn btn-primary" type="submit" name="submit_vote" value="' . JText::_('PLG_VOTE_RATE') . '" />';
				$html .= '<input type="hidden" name="task" value="article.vote" />';
				$html .= '<input type="hidden" name="hitcount" value="0" />';
				$html .= '<input type="hidden" name="url" value="' . htmlspecialchars($uri->toString(), ENT_COMPAT, 'UTF-8') . '" />';
				$html .= JHtml::_('form.token');
				$html .= '</form></div>';
			}
		}

		return $html;
	}
}