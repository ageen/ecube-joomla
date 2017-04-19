<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.ECUBE
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

JText::script('TPL_ECUBE_ALTOPEN');
JText::script('TPL_ECUBE_ALTCLOSE');
JText::script('TPL_ECUBE_TEXTRIGHTOPEN');
JText::script('TPL_ECUBE_TEXTRIGHTCLOSE');
JText::script('TPL_ECUBE_FONTSIZE');
JText::script('TPL_ECUBE_BIGGER');
JText::script('TPL_ECUBE_RESET');
JText::script('TPL_ECUBE_SMALLER');
JText::script('TPL_ECUBE_INCREASE_SIZE');
JText::script('TPL_ECUBE_REVERT_STYLES_TO_DEFAULT');
JText::script('TPL_ECUBE_DECREASE_SIZE');
JText::script('TPL_ECUBE_OPENMENU');
JText::script('TPL_ECUBE_CLOSEMENU');

$this->addScriptDeclaration("
	var big        = '" . (int) $this->params->get('wrapperLarge') . "%';
	var small      = '" . (int) $this->params->get('wrapperSmall') . "%';
	var bildauf    = '" . $this->baseurl . "/templates/" . $this->template . "/images/plus.png';
	var bildzu     = '" . $this->baseurl . "/templates/" . $this->template . "/images/minus.png';
	var rightopen  = '" . JText::_('TPL_ECUBE_TEXTRIGHTOPEN', true) . "';
	var rightclose = '" . JText::_('TPL_ECUBE_TEXTRIGHTCLOSE', true) . "';
	var altopen    = '" . JText::_('TPL_ECUBE_ALTOPEN', true) . "';
	var altclose   = '" . JText::_('TPL_ECUBE_ALTCLOSE', true) . "';
");
