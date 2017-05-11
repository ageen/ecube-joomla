<?php
//no direct access
defined('_JEXEC') or die('Direct Access to this location is not allowed.');
// Include the helper functions only once
JLoader::register('ModJeSocialHelper', __DIR__ . '/helper.php');

// Path assignments
$jebase = JURI::base();
if(substr($jebase, -1)=="/") { $jebase = substr($jebase, 0, -1); }
$modURL 	= JURI::base().'modules/mod_je_social';
$iconSize= $params->get('iconSize',"24");
$Icon[]= $params->get( '!', "" );
for ($j=1; $j<=30; $j++){
	$Icon[]		= $params->get( 'Icon'.$j , "" );
}

$social = array ("","evernotecn","h163","mshare","tsina","qzone","renren","tqq","kaixin001","tieba","sqq","bdysc","huaban","youdao","wealink","ty","duitang","weixin");

$item = ModJeSocialHelper::getItem();
$title = trim($item->title);
$introtext = trim($item->introtext);
$introimage = json_decode($item->images)->image_intro;
$uri = JUri::getInstance();
$url_base = dirname($uri);
?>

<div class="bdsharebuttonbox" data-tag="share_1">
	<?php for ($i=1; $i<=30; $i++){ if ($Icon[$i] != 0) { ?>
       <a class="bds_<?php echo $social[$i];?>" data-cmd=<?php echo $social[$i];?>></a>
    <?php }};  ?>
</div>

<script type="text/javascript">
	window._bd_share_config = {
		common : {
			bdText : '<?php echo $title;?>',	
			bdDesc : '<?php echo strip_tags($introtext);?>',
			bdUrl : '<?php echo $uri;?>', 
			bdPic : '<?php echo $url_base."/".$introimage;?>'
		},
		share : [{
			"bdSize" : <?php echo $iconSize;?>
		}],
	}
	with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?cdnversion='+~(-new Date()/36e5)];
</script>