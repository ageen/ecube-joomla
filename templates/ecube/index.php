<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.ECUBE
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
JLoader::import('joomla.filesystem.file');

// Check modules
$showRightColumn = ($this->countModules('position-3') or $this->countModules('position-6') or $this->countModules('position-8'));
$showbottom      = ($this->countModules('position-9') or $this->countModules('position-10') or $this->countModules('position-11'));
$showleft        = ($this->countModules('position-4') or $this->countModules('position-7') or $this->countModules('position-5'));

if ($showRightColumn == 0 and $showleft == 0)
{
	$showno = 0;
}

// Get params
$color          = $this->params->get('templatecolor');
$logo           = $this->params->get('logo');
$navposition    = $this->params->get('navposition');
$headerImage    = $this->params->get('headerImage');
$config         = JFactory::getConfig();
$option         = JFactory::getApplication()->input->getCmd('option', '');

// Output as HTML5
$this->setHtml5(true);
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
	    <meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
		<meta name="HandheldFriendly" content="true" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="format-detection" content="telephone=no" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
	    <title>e魔坊工作室</title>
	    <link rel="shortcut icon" href="favicon.ico">
	    <link href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
	    <link href="/templates/ecube/css/general.css" rel="stylesheet" media="screen">
	    <link href="/templates/ecube/css/reset.css" rel="stylesheet" media="screen">
		<!--[if IE 7]><link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/ie7only.css" rel="stylesheet" /><![endif]-->
		<!--[if lt IE 9]><script src="<?php echo JUri::root(true); ?>/media/jui/js/html5.js"></script><![endif]-->
	</head>
	<body>
	<div class="header"><jdoc:include type="modules" name="topmenu" /></div>

<div id="myCarousel"  class="carousel slide carousel-fade" data-ride="carousel">
<!-- Wrapper for slides -->
	<div class="carousel-inner" role="listbox">
		<div class="item active">
			<a href="包含图片一文章路径"><img src="templates/ecube/images/slider/1.jpg" alt="图片一" /></a>
	    	<div class="carousel-caption">
	            <h4 class="alpha">
	                <a style="color:white;" href="包含图片一文章路径">图片一标题</a>
	        	</h4>
	        </div>
      	</div>

    	<div class="item">
    		<a href="包含图片二文章路径"><img src="templates/ecube/images/slider/1.jpg" alt="图片二" /></a>
    		<div class="carousel-caption">
            	<h4 class="alpha">
                  <a style="color:white;" href="包含图片二文章路径">图片二标题</a>
              	</h4>
            </div>
     	</div>
     <div class="item">
         <a href="包含图片三文章路径">
             <img src="templates/ecube/images/slider/1.jpg" alt="图片三" />
          </a>
          <div class="carousel-caption">
              <h4 class="alpha">
                  <a style="color:white;" href="包含图片三文章路径">图片三标题</a>
              </h4>
             </div>
     </div>
        <!-- Carousel controls -->
        <a class="carousel-control left" href="#myCarousel" data-slide="prev">&nbsp;</a>
        <a class="carousel-control right" href="#myCarousel" data-slide="next">&nbsp;</a>
  </div>
</div>

		<div id="all">
			<div id="back">
				<header id="header">
					<div class="logoheader">
						<h1 id="logo">
						<?php if ($logo) : ?>
							<img src="<?php echo $this->baseurl; ?>/<?php echo htmlspecialchars($logo); ?>"  alt="<?php echo htmlspecialchars($this->params->get('sitetitle')); ?>" />
						<?php endif;?>
						<?php if (!$logo AND $this->params->get('sitetitle')) : ?>
							<?php echo htmlspecialchars($this->params->get('sitetitle')); ?>
						<?php elseif (!$logo AND $config->get('sitename')) : ?>
							<?php echo htmlspecialchars($config->get('sitename')); ?>
						<?php endif; ?>
						<span class="header1">
						<?php echo htmlspecialchars($this->params->get('sitedescription')); ?>
						</span></h1>
					</div><!-- end logoheader -->
					<ul class="skiplinks">
						<li><a href="#main" class="u2"><?php echo JText::_('TPL_ECUBE_SKIP_TO_CONTENT'); ?></a></li>
						<li><a href="#nav" class="u2"><?php echo JText::_('TPL_ECUBE_JUMP_TO_NAV'); ?></a></li>
						<?php if ($showRightColumn) : ?>
							<li><a href="#right" class="u2"><?php echo JText::_('TPL_ECUBE_JUMP_TO_INFO'); ?></a></li>
						<?php endif; ?>
					</ul>
					<h2 class="unseen"><?php echo JText::_('TPL_ECUBE_NAV_VIEW_SEARCH'); ?></h2>
					<h3 class="unseen"><?php echo JText::_('TPL_ECUBE_NAVIGATION'); ?></h3>
					<jdoc:include type="modules" name="position-1" />
				</header><!-- end header -->
				<div id="<?php echo $showRightColumn ? 'contentarea2' : 'contentarea'; ?>">
					<div id="breadcrumbs">
						<jdoc:include type="modules" name="position-2" />
					</div>

					<?php if ($navposition == 'left' and $showleft) : ?>
						<nav class="left1 <?php if ($showRightColumn == null) { echo 'leftbigger';} ?>" id="nav">
							<jdoc:include type="modules" name="position-7" style="beezDivision" headerLevel="3" />
							<jdoc:include type="modules" name="position-4" style="beezHide" headerLevel="3" state="0 " />
							<jdoc:include type="modules" name="position-5" style="beezTabs" headerLevel="2"  id="3" />
						</nav><!-- end navi -->
					<?php endif; ?>

					<div id="<?php echo $showRightColumn ? 'wrapper' : 'wrapper2'; ?>" <?php if (isset($showno)){echo 'class="shownocolumns"';}?>>
						<div id="main">

							<?php if ($this->countModules('position-12')) : ?>
								<div id="top">
									<jdoc:include type="modules" name="position-12" />
								</div>
							<?php endif; ?>

							<jdoc:include type="message" />
							<jdoc:include type="component" />

						</div><!-- end main -->
					</div><!-- end wrapper -->

					<?php if ($showRightColumn) : ?>
						<div id="close">
							<a href="#" onclick="auf('right')">
							<span id="bild">
								<?php echo JText::_('TPL_ECUBE_TEXTRIGHTCLOSE'); ?>
							</span>
							</a>
						</div>

						<aside id="right">
							<h2 class="unseen"><?php echo JText::_('TPL_ECUBE_ADDITIONAL_INFORMATION'); ?></h2>
							<jdoc:include type="modules" name="position-6" style="beezDivision" headerLevel="3" />
							<jdoc:include type="modules" name="position-8" style="beezDivision" headerLevel="3" />
							<jdoc:include type="modules" name="position-3" style="beezDivision" headerLevel="3" />
						</aside><!-- end right -->
					<?php endif; ?>

					<?php if ($navposition == 'center' and $showleft) : ?>
						<nav class="left <?php if ($showRightColumn == null) { echo 'leftbigger'; } ?>" id="nav" >

							<jdoc:include type="modules" name="position-7"  style="beezDivision" headerLevel="3" />
							<jdoc:include type="modules" name="position-4" style="beezHide" headerLevel="3" state="0 " />
							<jdoc:include type="modules" name="position-5" style="beezTabs" headerLevel="2"  id="3" />

						</nav><!-- end navi -->
					<?php endif; ?>

					<div class="wrap"></div>
				</div> <!-- end contentarea -->
			</div><!-- back -->
		</div><!-- all -->
		<div id="footer-outer">
			<?php if ($showbottom) : ?>
				<div id="footer-inner" >

					<div id="bottom">
						<div class="box box1"> <jdoc:include type="modules" name="position-9" style="beezDivision" headerlevel="3" /></div>
						<div class="box box2"> <jdoc:include type="modules" name="position-10" style="beezDivision" headerlevel="3" /></div>
						<div class="box box3"> <jdoc:include type="modules" name="position-11" style="beezDivision" headerlevel="3" /></div>
					</div>

				</div>
			<?php endif; ?>

			<div id="footer-sub">
				<footer id="footer">
					<jdoc:include type="modules" name="position-14" />
				</footer><!-- end footer -->
			</div>
		</div>

<script src="https://cdn.bootcss.com/jquery/3.2.0/jquery.min.js"></script>
<script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="/templates/ecube/javascript/jqthumb.min.js"></script>
<script src="/templates/ecube/javascript/customer.js"></script>
	</body>
</html>
