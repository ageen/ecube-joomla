<?php     
 $facebook	=	htmlspecialchars($this->params->get('facebook')); 
 $twitter	=	htmlspecialchars($this->params->get('twitter')); 
 $gplus	=	htmlspecialchars($this->params->get('gplus'));  
 $mail	=	htmlspecialchars($this->params->get('mail')); 
?> 

<a href="<?php echo htmlspecialchars($facebook); ?>"><img src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/images/facebook.png" alt="f" ></a>
<a href="<?php echo htmlspecialchars($twitter); ?>"><img src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/images/twitter.png" alt="t" ></a>
<a href="<?php echo htmlspecialchars($gplus); ?>"><img src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/images/gplus.png" alt="g" ></a>
<a href="<?php echo htmlspecialchars($mail); ?>"><img src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/images/mail.png" alt="m" ></a>




