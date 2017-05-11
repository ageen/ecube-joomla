<?php

/**
 * @package		Joomla.Site
 * @subpackage	mod_je_socialprofiles
 * @copyright	Copyright (C) 2004 - 2015 jExtensions.com - All rights reserved.
 * @license		GNU General Public License version 2 or later
 */

 // no direct access

defined('JPATH_BASE') or die;
jimport('joomla.form.formfield');

class JFormFieldCheckbox extends JFormField 

{
	protected $type = 'checkbox';
	public function getLabel() {
			$label = '';
			// Get the label text from the XML element, defaulting to the element name.
			$text = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];
			// Build the class for the label.
			$class = !empty($this->description) ? 'hasTip hasTooltip' : '';
			$class = $this->required == true ? $class.' required' : $class;
			$icon_class = str_replace("jform_params_","",$this->id);
			// Add replace checkbox
			$replace = '<span id="Icon" class="'.$icon_class.'"></span>';	
			// Add the opening label tag and main attributes attributes.
			$label .= '<label id="'.$this->id.'" for="'.$this->id.'" class="'.$class.'"';
			// If a description is specified, use it to build a tooltip.
			if (!empty($this->description)) {
					$label .= ' title="'.htmlspecialchars(trim(JText::_($text), ':').'::' .
									JText::_($this->description), ENT_COMPAT, 'UTF-8').'"';
			}
			// Add the label text and closing tag.
			$label .= '>'.$replace.'</label>';
			return $label; 
	}
	protected function getInput()
	{
		if($this->value == 1){
			return '<input type="checkbox" name="'.$this->name.'" id="'.$this->id.'"'.' checked value=1 class=""/>';
		}else{
			return '<input type="checkbox" name="'.$this->name.'" id="'.$this->id.'"'.' value=1 class=""/>';
		}
		
	}		
}
?>