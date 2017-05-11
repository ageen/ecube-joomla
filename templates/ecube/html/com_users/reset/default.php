<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
// to reset the form xml loaded by the view
$this->form->reset( true );

// to load in our own version of default_login.xml
$this->form->loadFile( dirname(__FILE__) ."/reset_request.xml");

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
?>
<div class="container reset<?php echo $this->pageclass_sfx?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	</div>
	<?php endif; ?>

	<form id="user-registration" action="<?php echo JRoute::_('index.php?option=com_users&task=reset.request'); ?>" method="post" class="form-validate form-horizontal">
		<?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
			<fieldset>
				<p><?php echo JText::_($fieldset->label); ?></p>
				<?php foreach ($this->form->getFieldset($fieldset->name) as $name => $field) : ?>
					<div class="form-group">
						<div class="control-label col-sm-2 col-xs-12">
							<?php echo $field->label; ?>
						</div>
						<div class="col-sm-10 col-xs-12">
							<?php echo $field->input; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</fieldset>
		<?php endforeach; ?>
		<hr class="easycalccheck" />
		<div class="control-group">
			<div class="controls">
				<button type="submit" class="btn btn-primary validate btn-block" style="max-width: 600px;margin: 0 auto;"><?php echo JText::_('JSUBMIT'); ?></button>
			</div>
		</div>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
