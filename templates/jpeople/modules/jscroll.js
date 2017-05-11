			jQuery(function() {
				var $elem = jQuery('#content');
	
				jQuery('#nav_up').click(
					function (e) {
						jQuery('html, body').animate({scrollTop: '0px'}, 800);
					}
				);
            });
			
	jQuery(window).scroll(function() {

    if (jQuery(this).scrollTop()> 200)
     {
        jQuery('.nav_up').fadeIn();
     }
    else
     {
      jQuery('.nav_up').fadeOut();
     }
 });
 