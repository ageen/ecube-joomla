<?php
defined('_JEXEC') or die;

require_once __DIR__ . '/helper.php';

$list = mod_latestextenstionsHelper::getList($params);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
require JModuleHelper::getLayoutPath('mod_latestextensions', $params->get('layout', 'default'));