<?php
  $clsPath = realpath(dirname(__FILE__).'/src/main/php');
  set_include_path(get_include_path() . PATH_SEPARATOR . $clsPath);

  require('lang.base.php');

  uses(
    'de.gamepay.xpbug.circularreference.XPBugCircularReference'
  );
?>
