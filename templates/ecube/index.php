<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.ecube
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

// Output as HTML5
$doc->setHtml5(true);

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

unset($doc->_scripts["/media/jui/js/jquery.min.js"]);
unset($doc->_scripts["/media/jui/js/bootstrap.min.js"]);
// Add Stylesheets
$doc->addStyleSheet($this->baseurl.'/media/jui/css/icomoon.css');
$doc->addStyleSheetVersion('//cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css');
$doc->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/print.css', 'text/css', 'print');

// Adjusting content width
if ($this->countModules('position-7') && $this->countModules('position-8'))
{
	$span = "span6";
}
elseif ($this->countModules('position-7') && !$this->countModules('position-8'))
{
	$span = "span9";
}
elseif (!$this->countModules('position-7') && $this->countModules('position-8'))
{
	$span = "span9";
}
else
{
	$span = "span12";
}
// Logo file or site title param
if ($this->params->get('logoFile'))
{
	$logo = '<img src="' . JUri::root() . $this->params->get('logoFile') . '" alt="' . $sitename . '" />';
}
elseif ($this->params->get('sitetitle'))
{
	$logo = '<span class="site-title" title="' . $sitename . '">' . htmlspecialchars($this->params->get('sitetitle'), ENT_COMPAT, 'UTF-8') . '</span>';
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
	<jdoc:include type="head" name="head" />
	<link href="/templates/ecube/css/general.css" rel="stylesheet" />
	<link href="/templates/ecube/css/querymedia.css" rel="stylesheet" />
	<!--[if lt IE 9]><script src="<?php echo JUri::root(true); ?>/media/jui/js/html5.js"></script><![endif]-->
	<script src="//cdn.bootcss.com/jquery/3.2.0/jquery.min.js"></script>
	<script src="//cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<jdoc:include type="head" name="foot" />
	<script src="/templates/ecube/js/template.js"></script>
</head>
<body class="site <?php echo $option
	. ' view-' . $view
	. ($layout ? ' layout-' . $layout : ' no-layout')
	. ($task ? ' task-' . $task : ' no-task')
	. ($itemid ? ' itemid-' . $itemid : '')
	. ($params->get('fluidContainer') ? ' fluid' : '');
	echo ($this->direction == 'rtl' ? ' rtl' : '');
?>"  ontouchstart>
	<div class="header"><jdoc:include type="modules" name="topmenu" /></div>
	<!-- Body -->
	<div class="body">
		<div class="container<?php echo ($params->get('fluidContainer') ? '-fluid' : ''); ?>">
			<!-- Header -->

			<jdoc:include type="modules" name="banner" style="xhtml" />
			<div class="row-fluid">
				<?php if ($this->countModules('position-8')) : ?>
					<!-- Begin Sidebar -->
					<div class="row" style="margin: 15px auto;max-width: 1300px;">
						<div class="col-md-4">
							<jdoc:include type="modules" name="position-8" style="xhtml" />
						</div>
						<div class="col-md-8">
							<jdoc:include type="modules" name="position-10" style="xhtml" />	
						</div>
					</div>
					<div class="clearfix"></div>
					<!-- End Sidebar -->
				<?php endif; ?>
				<main id="content" role="main" class="<?php echo $span; ?>">
					<!-- Begin Content -->
					<jdoc:include type="modules" name="position-3" style="xhtml" />
					<?php 
						if(count($app->getMessageQueue())) {
					?>
					<jdoc:include type="message" />
					<?php
						}
					?>
					<jdoc:include type="component" />
					<!-- End Content -->
				</main>
				<jdoc:include type="modules" name="position-2" style="none" />
				<?php if ($this->countModules('position-7')) : ?>
					<div id="aside" class="span3">
						<!-- Begin Right Sidebar -->
						<jdoc:include type="modules" name="position-7" style="well" />
						<!-- End Right Sidebar -->
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div><jdoc:include type="modules" name="position-4" style="well" /></div>
	<div style="position: fixed;bottom: 50px; right: 20px;z-index: 999;" class="text-center unseen"><a class="back-to-top" href="javascript:void(0);"><span class="glyphicon glyphicon-chevron-up" style="font-size: 16px;"></span></a><br/>
<a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=1612277328&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:1612277328:52" alt="如有任何相关疑问，请咨询我" title="如有任何相关疑问，请咨询我"/></a>
	</div>
	<!-- Footer -->
	<footer class="footer" role="contentinfo">
		<div class="container<?php echo ($params->get('fluidContainer') ? '-fluid' : ''); ?>">
			<div class="text-center">
				<jdoc:include type="modules" name="footer" style="none" />
			</div>
		</div>
	</footer>
	<jdoc:include type="modules" name="debug" style="none" />

</body>
</html>