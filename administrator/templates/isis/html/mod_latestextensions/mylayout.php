<?php
defined('_JEXEC') or die;
?>
<div class="latestextensions<?php echo $moduleclass_sfx ?>">
<div class="row-striped">
<?php foreach ($list as $item) : ?>
<div class="row-fluid">
<div class="span9">
<strong class="row-title">
<?php echo $item->name; ?> (<?php echo $item->type;?>)
</strong>
</div>
<div class="span3">
<?php echo $item->type; ?>
</div>
</div>
<?php endforeach; ?>
</div>
</div>