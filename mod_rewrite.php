<?php
/**
 * @title Check if Apache's mod_rewrite is installed.
 * 
 * @author Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright (c) 2013, Pierre-Henry Soria. All Rights Reserved.
 * @return boolean
 */
function isRewriteMod()
{
  if (function_exists('apache_get_modules'))
  {
    $aMods = apache_get_modules();
    $bIsRewrite = in_array('mod_rewrite', $aMods);
  }
  else
  {
    $bIsRewrite = (strtolower(getenv('HTTP_MOD_REWRITE')) == 'on');
  }
  return $bIsRewrite;
}

if (!isRewriteMod()) exit('Please install Apache mod_rewrite module.');