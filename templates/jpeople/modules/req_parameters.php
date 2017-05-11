<?php
defined( '_JEXEC' ) or die( 'Restricted access' ); 
JLoader::import('joomla.filesystem.file');
JHtml::_('behavior.framework', true);
JHtml::_('bootstrap.framework');
$document = JFactory::getDocument();

$slogan	= 			$this->params->get("slogan");
$slogandisable	= 	$this->params->get("slogandisable");
$addthis	= 		$this->params->get("addthis");
$footertext	= 		$this->params->get("footertext");
$footerdisable	= 	$this->params->get("footerdisable");
$googleanalytics = 	$this->params->get("googleanalytics");
$analyticsdisable = $this->params->get("analyticsdisable");
$socialdisable	= 	$this->params->get("socialdisable");
$facebook	= 		$this->params->get("facebook");
$twitter	= 		$this->params->get("twitter");
$gplus	= 		$this->params->get("gplus");
$jscroll	= 		$this->params->get("jscroll");
$slidehome	= 		$this->params->get("slidehome");
$slidedesc1	= 		$this->params->get("slidedesc1");
$slidedesc2	= 		$this->params->get("slidedesc2");
$slidedesc3	= 		$this->params->get("slidedesc3");
$slidedesc4	= 		$this->params->get("slidedesc4");
$slidedesc5	= 		$this->params->get("slidedesc5");
$slidedesc6	= 		$this->params->get("slidedesc6");
$slidedesc7	= 		$this->params->get("slidedesc7");
$slidedesc8	= 		$this->params->get("slidedesc8");

$logo			= $this->params->get('logo');
$logotype		= $this->params->get('logotype');
$sitetitle		= $this->params->get('sitetitle');
?>