 <script>
    // You can also use "$(window).load(function() {"
    jQuery(function () {
      jQuery("#slider4").responsiveSlides({
        auto: true,
        pager: false,
        nav: true,
        speed: 500,
        namespace: "callbacks",
        before: function () {
          jQuery('.events').append("<li>before event fired.</li>");
        },
        after: function () {
          jQuery('.events').append("<li>after event fired.</li>");
        }
      });

    });
</script>

<?php     
 $slide1	=	htmlspecialchars($this->params->get('slide1')); 
 $slide2	=	htmlspecialchars($this->params->get('slide2')); 
 $slide3	=	htmlspecialchars($this->params->get('slide3'));  
 $slide4	=	htmlspecialchars($this->params->get('slide4')); 
 $slide5	=	htmlspecialchars($this->params->get('slide5')); 
 $slide6	=	htmlspecialchars($this->params->get('slide6')); 
 $slide7	=	htmlspecialchars($this->params->get('slide7'));  
 $slide8	=	htmlspecialchars($this->params->get('slide8'));
 
 $NumberOfSlides	=	htmlspecialchars($this->params->get('NumberOfSlides'));
?> 


    <div class="callbacks_container">
      <ul class="rslides" id="slider4">

		<?php if ($slide1 != null ) : ?><li><img src="<?php echo $this->baseurl ?>/<?php echo htmlspecialchars($slide1); ?>" /><p class="caption"><?php echo ($slidedesc1); ?></p></li>
		<?php else : ?><li><img src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/slideshow/1.jpg" alt="1" /><p class="caption"><?php if ($this->params->get( 'slidedesc1' )) : ?><?php echo ($slidedesc1); ?><?php endif; ?></p></li><?php endif; ?>
		<?php if ($slide2 != null ) : ?><li><img src="<?php echo $this->baseurl ?>/<?php echo htmlspecialchars($slide2); ?>" /><p class="caption"><?php echo ($slidedesc2); ?></p></li>
		<?php else : ?><li><img src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/slideshow/2.jpg" alt="2" /><p class="caption"><?php if ($this->params->get( 'slidedesc2' )) : ?><?php echo ($slidedesc2); ?><?php endif; ?></p></li><?php endif; ?>
		<?php if ($slide3 != null ) : ?><li><img src="<?php echo $this->baseurl ?>/<?php echo htmlspecialchars($slide3); ?>" /><p class="caption"><?php echo ($slidedesc3); ?></p></li>
		<?php else : ?><li><img src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/slideshow/3.jpg" alt="3" /><p class="caption"><?php if ($this->params->get( 'slidedesc3' )) : ?><?php echo ($slidedesc3); ?><?php endif; ?></p></li><?php endif; ?>
		<?php if ($slide4 != null ) : ?><li><img src="<?php echo $this->baseurl ?>/<?php echo htmlspecialchars($slide4); ?>" /><p class="caption"><?php echo ($slidedesc4); ?></p></li>
		<?php else : ?><li><img src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/slideshow/4.jpg" alt="4" /><p class="caption"><?php if ($this->params->get( 'slidedesc4' )) : ?><?php echo ($slidedesc4); ?><?php endif; ?></p></li><?php endif; ?>
		
		<?php if ($NumberOfSlides >= 5 ) : ?><?php if ($slide5 != null ) : ?><li><img src="<?php echo $this->baseurl ?>/<?php echo htmlspecialchars($slide5); ?>" /><p class="caption"><?php echo ($slidedesc5); ?></p></li><?php endif; ?><?php endif; ?>
		<?php if ($NumberOfSlides >= 6 ) : ?><?php if ($slide6 != null ) : ?><li><img src="<?php echo $this->baseurl ?>/<?php echo htmlspecialchars($slide6); ?>" /><p class="caption"><?php echo ($slidedesc6); ?></p></li><?php endif; ?><?php endif; ?>
		<?php if ($NumberOfSlides >= 7 ) : ?><?php if ($slide7 != null ) : ?><li><img src="<?php echo $this->baseurl ?>/<?php echo htmlspecialchars($slide7); ?>" /><p class="caption"><?php echo ($slidedesc7); ?></p></li><?php endif; ?><?php endif; ?>
		<?php if ($NumberOfSlides >= 8 ) : ?><?php if ($slide8 != null ) : ?><li><img src="<?php echo $this->baseurl ?>/<?php echo htmlspecialchars($slide8); ?>" /><p class="caption"><?php echo ($slidedesc8); ?></p></li><?php endif; ?><?php endif; ?>
		
	  </ul>
    </div>



