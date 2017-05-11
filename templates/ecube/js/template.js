/**
 * @package     Joomla.Site
 * @subpackage  Templates.protostar
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       3.2
 */

(function($)
{
	$(document).ready(function(){
	  var $backToTop = $(".back-to-top");
	  /* 隐藏回顶部按钮 */
	  $backToTop.hide();
	 
	  $(window).on('scroll', function() {
	   if ($(this).scrollTop() > 100) { /* 返回顶部按钮将在用户向下滚动100像素后出现 */
	   $backToTop.fadeIn();
	   } else {
	   $backToTop.fadeOut();
	   }
	  });
	 
	  $backToTop.on('click', function(e) {
	   $("html, body").animate({scrollTop: 0}, 500);
	  });

	  $("a.dropdown-toggle").hover(function(){
	  	$(this).parent().addClass("open");
	  });

	  $("div.intro-more").click(function(){
	  	console.log($(this).parent().next("dd.list-intro").css("display"))
	  	if($(this).parent().next("dd.list-intro").css("display") == "none"){
	  		$(this).children().attr("class", "glyphicon glyphicon-menu-up");
	  		$(this).parent().next("dd.list-intro").fadeIn();
	  	}else{
	  		$(this).children().attr("class", "glyphicon glyphicon-menu-down");
	  		$(this).parent().next("dd.list-intro").fadeOut();
	  	}
	  });
	})
})(jQuery);