<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.protostar
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$twofactormethods = JAuthenticationHelper::getTwoFactorMethods();
$app              = JFactory::getApplication();
$doc              = JFactory::getDocument();
$this->language   = $doc->language;
$this->direction  = $doc->direction;

// Output as HTML5
$doc->setHtml5(true);

$fullWidth = 1;


// Add Stylesheets
$doc->addStyleSheetVersion('//cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css');
$doc->addStyleSheetVersion($this->baseurl . '/templates/' . $this->template . '/css/offline.css');

// Check for a custom CSS file
$userCss = JPATH_SITE . '/templates/' . $this->template . '/css/user.css';

if (file_exists($userCss) && filesize($userCss) > 0)
{
	$doc->addStyleSheetVersion($this->baseurl . '/templates/' . $this->template . '/css/user.css');
}

// Load optional RTL Bootstrap CSS
JHtml::_('bootstrap.loadCss', false, $this->direction);

// Logo file or site title param
$sitename = $app->get('sitename');

if ($this->params->get('logoFile'))
{
	$logo = '<img src="' . JUri::root() . $this->params->get('logoFile') . '" alt="' . $sitename . '" />';
}
elseif ($this->params->get('sitetitle'))
{
	$logo = '<span class="site-title" title="' . $sitename . '">' . htmlspecialchars($this->params->get('sitetitle')) . '</span>';
}
else
{
	$logo = '<span class="site-title" title="' . $sitename . '">' . $sitename . '</span>';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<jdoc:include type="head" />
	<!--[if lt IE 9]><script src="<?php echo JUri::root(true); ?>/media/jui/js/html5.js"></script><![endif]-->
</head>
<body class="site">
	<div class="outer">
		<div class="middle">
			<div class="inner well">
				<div class="header">
				<?php if (!empty($logo)) : ?>
					<h3><?php echo $logo; ?></h3>
				<?php else : ?>
					<h3><?php echo htmlspecialchars($app->get('sitename')); ?></h3>
				<?php endif; ?>
				<?php if ($app->get('offline_image') && file_exists($app->get('offline_image'))) : ?>
					<img src="<?php echo $app->get('offline_image'); ?>" alt="<?php echo htmlspecialchars($app->get('sitename')); ?>" />
				<?php endif; ?>
				<?php if ($app->get('display_offline_message', 1) == 1 && str_replace(' ', '', $app->get('offline_message')) != '') : ?>
					<p><?php echo $app->get('offline_message'); ?></p>
				<?php elseif ($app->get('display_offline_message', 1) == 2) : ?>
					<p><?php echo JText::_('JOFFLINE_MESSAGE'); ?></p>
				<?php endif; ?>
				</div>
				<jdoc:include type="message" style="alert-warning" />
				<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" id="form-login">
					<fieldset>
						<label for="username"><?php echo JText::_('JGLOBAL_USERNAME'); ?></label>
						<input name="username" id="username" type="text" title="<?php echo JText::_('JGLOBAL_USERNAME'); ?>" class="form-control" />

						<label for="password"><?php echo JText::_('JGLOBAL_PASSWORD'); ?></label>
						<input type="password" name="password" id="password" title="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>" class="form-control" />

						<?php if (count($twofactormethods) > 1) : ?>
						<label for="secretkey"><?php echo JText::_('JGLOBAL_SECRETKEY'); ?></label>
						<input type="text" name="secretkey" id="secretkey" title="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>" />
						<?php endif; ?>

						<input type="submit" name="Submit" class="btn btn-primary btn-block" value="<?php echo JText::_('JLOGIN'); ?>" />

						<input type="hidden" name="option" value="com_users" />
						<input type="hidden" name="task" value="user.login" />
						<input type="hidden" name="return" value="<?php echo base64_encode(JUri::base()); ?>" />
						<?php echo JHtml::_('form.token'); ?>
					</fieldset>
				</form>
			</div>
		</div>
	</div>
</body>
</html>
