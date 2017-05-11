<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.protostar
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
require_once __DIR__ .'/html/renderer/head.php';

$app             = JFactory::getApplication();
$doc             = JFactory::getDocument();
$user            = JFactory::getUser();
$this->language  = $doc->language;
$this->direction = $doc->direction;

// Output document as HTML5.
if (is_callable(array($doc, 'setHtml5')))
{
	$doc->setHtml5(true);
}

// Getting params from template
$params = $app->getTemplate(true)->params;

// Detecting Active Variables
$option   = $app->input->getCmd('option', '');
$view     = $app->input->getCmd('view', '');
$layout   = $app->input->getCmd('layout', '');
$task     = $app->input->getCmd('task', '');
$itemid   = $app->input->getCmd('Itemid', '');
$sitename = $app->get('sitename');

if($task == "edit" || $layout == "form" )
{
	$fullWidth = 1;
}
else
{
	$fullWidth = 0;
}

// Add JavaScript Frameworks
//JHtml::_('bootstrap.framework');
// clear default js library
$doc->_scripts = [];
$doc->addScriptVersion('https://cdn.bootcss.com/jquery/3.2.0/jquery.min.js');
$doc->addScriptVersion('https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js');
$doc->addScriptVersion($this->baseurl . '/templates/' . $this->template . '/js/template.js');

// Add Stylesheets
$doc->addStyleSheetVersion('https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css');
$doc->addStyleSheetVersion($this->baseurl . '/templates/' . $this->template . '/css/general.css');
$doc->addStyleSheetVersion($this->baseurl . '/templates/' . $this->template . '/css/querymedia.css');

// Logo file or site title param
if ($params->get('logoFile'))
{
	$logo = '<img src="' . JUri::root() . $params->get('logoFile') . '" alt="' . $sitename . '" />';
}
elseif ($params->get('sitetitle'))
{
	$logo = '<span class="site-title" title="' . $sitename . '">' . htmlspecialchars($params->get('sitetitle')) . '</span>';
}
else
{
	$logo = '<span class="site-title" title="' . $sitename . '">' . $sitename . '</span>';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
	<?php echo $doc->getBuffer('head', 'head', array('style' => 'none')); ?>
	<!--[if lt IE 9]><script src="<?php echo JUri::root(true); ?>/media/jui/js/html5.js"></script><![endif]-->
</head>
<body class="site <?php echo $option
	. ' view-' . $view
	. ($layout ? ' layout-' . $layout : ' no-layout')
	. ($task ? ' task-' . $task : ' no-task')
	. ($itemid ? ' itemid-' . $itemid : '')
	. ($params->get('fluidContainer') ? ' fluid' : '');
?>">
<div class="header">
<?php echo $doc->getBuffer('modules', 'topmenu', array('style' => 'none')); ?>
</div>
	<!-- Body -->
	<div class="container body">
		<div class="container<?php echo ($params->get('fluidContainer') ? '-fluid' : ''); ?>">

			<div class="navigation">
				<?php // Display position-1 modules ?>
				<?php echo $doc->getBuffer('modules', 'position-1', array('style' => 'none')); ?>
			</div>
			<!-- Banner -->
			<div class="banner">
				<?php echo $doc->getBuffer('modules', 'banner', array('style' => 'xhtml')); ?>
			</div>
<section class="simple-page-content" style="margin-top: 15px;"><pre style="background:#fff;"><big>
<span style="color: #0000ff;">( <?php echo JText::_('JERROR_LAYOUT_PAGE_NOT_FOUND'); ?> )</span>
<span style="color: #ff0000;">#<?php echo $this->error->getCode(); ?>&nbsp;<?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></span>
 ------------- 
       O 
        O   ^__^
         o  (oo)\_______
            (__)\       )\/\
                ||----w |
                ||     ||
</big></pre></section>
		</div>
	</div>
	<?php if ($this->debug) : ?>
	<div class="wrapper" style="padding:10px;">
	    <?php echo $this->renderBacktrace(); ?>
		<?php // Check if there are more Exceptions and render their data as well ?>
		<?php if ($this->error->getPrevious()) : ?>
			<?php $loop = true; ?>
			<?php // Reference $this->_error here and in the loop as setError() assigns errors to this property and we need this for the backtrace to work correctly ?>
			<?php // Make the first assignment to setError() outside the loop so the loop does not skip Exceptions ?>
			<?php $this->setError($this->_error->getPrevious()); ?>
			<?php while ($loop === true) : ?>
				<p><strong><?php echo JText::_('JERROR_LAYOUT_PREVIOUS_ERROR'); ?></strong></p>
				<p><?php echo htmlspecialchars($this->_error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>
				<?php echo $this->renderBacktrace(); ?>
				<?php $loop = $this->setError($this->_error->getPrevious()); ?>
			<?php endwhile; ?>
			<?php // Reset the main error object to the base error ?>
			<?php $this->setError($this->error); ?>
		<?php endif; ?>
	</div>
	<?php endif;?>
	<!-- Footer -->
	<footer class="footer" role="contentinfo">
		<div class="container<?php echo ($params->get('fluidContainer') ? '-fluid' : ''); ?>">
			<div class="text-center">
				<?php echo $doc->getBuffer('modules', 'footer', array('style' => 'none')); ?>
			</div>
		</div>
	</footer>
	<?php echo $doc->getBuffer('modules', 'debug', array('style' => 'none')); ?>

	<?php echo $doc->getBuffer('head', 'foot', array('style' => 'none')); ?>
</body>
</html>
