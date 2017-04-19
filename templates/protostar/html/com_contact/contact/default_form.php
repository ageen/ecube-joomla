<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
// to reset the form xml loaded by the view
$this->form->reset( true );

// to load in our own version of contact.xml
$this->form->loadFile( dirname(__FILE__) ."/contact.xml");

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');

?>
<div class="contact-form">
	<form id="contact-form" action="<?php echo JRoute::_('index.php'); ?>" method="post" class="form-validate form-group well">
		<?php foreach ($this->form->getFieldsets() as $fieldset): ?>
			<?php if ($fieldset->name === 'captcha' && !$this->captchaEnabled) : ?>
				<?php continue; ?>
			<?php endif; ?>
			<?php $fields = $this->form->getFieldset($fieldset->name); ?>
			<?php if (count($fields)) : ?>
				<fieldset>
					<?php if (isset($fieldset->label) && strlen($legend = trim(JText::_($fieldset->label)))) : ?>
						<legend><?php echo $legend; ?></legend>
					<?php endif; ?>
					<?php foreach ($fields as $field) : ?>
						<?php if ($field->name === 'contact_email_copy' && !$this->params->get('show_email_copy')) : ?>
							<?php continue; ?>
						<?php endif; ?>
						<?php echo $field->renderField(); ?>
					<?php endforeach; ?>
				</fieldset>
			<?php endif; ?>
		<?php endforeach; ?>
		<div class="control-group">
			<div class="controls">
				<button class="btn btn-primary validate" type="submit"><?php echo JText::_('COM_CONTACT_CONTACT_SEND'); ?></button>
				<input type="hidden" name="option" value="com_contact" />
				<input type="hidden" name="task" value="contact.submit" />
				<input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
				<input type="hidden" name="id" value="<?php echo $this->contact->slug; ?>" />
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
	</form>
</div>
