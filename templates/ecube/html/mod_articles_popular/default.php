<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_popular
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="row mostread<?php echo $moduleclass_sfx; ?>">
<?php foreach ($list as $item) : ?>
	<div itemscope itemtype="https://schema.org/Article" class="item col-md-2 col-sm-4 col-xs-12">
		<a href="<?php echo $item->link; ?>" itemprop="url">
			<span itemprop="name">
				<?php echo $item->title; ?>
			</span>
		</a>
	</div>
<?php endforeach; ?>
</div>