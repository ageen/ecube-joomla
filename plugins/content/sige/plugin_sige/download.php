<?php
/**
 * @Copyright
 * @package     SIGE - Simple Image Gallery Extended for Joomla! 3.x
 * @author      Viktor Vogel <admin@kubik-rubik.de>
 * @version     3.2.3 - 2017-01-29
 * @link        https://joomla-extensions.kubik-rubik.de/sige-simple-image-gallery-extended
 *
 * @license     GNU/GPL
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

// Fast check whether image url variable was transmitted
if($_GET['img'] == '')
{
	exit('No parameters!');
}

$_GET['img'] = str_replace('..', '', htmlspecialchars(urldecode($_GET['img'])));
$image = rawurldecode('../../../..'.$_GET['img']);
$file = basename($image);

$file_extension = substr(strtolower($file), -3);
$allowed_extensions = array('jpg', 'gif', 'png');

if(in_array($file_extension, $allowed_extensions))
{
	$size = filesize($image);
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.$file);
	header('Content-Length:'.$size);
	readfile($image);
	exit();
}

exit($file.' is not an image type!');
