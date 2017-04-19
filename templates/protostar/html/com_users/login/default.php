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
$this->form->loadFile( dirname(__FILE__) ."/default_login.xml");

$cookieLogin = $this->user->get('cookieLogin');


if ($this->user->get('guest') || !empty($cookieLogin))
{
	// The user is not logged in or needs to provide a password.
	echo $this->loadTemplate('login');
}
else
{
	// The user is already logged in.
	echo $this->loadTemplate('logout');
}