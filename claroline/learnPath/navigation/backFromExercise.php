<?php
    // $Id$
/*
  +----------------------------------------------------------------------+
  | CLAROLINE version 1.3.2 $Revision$                            |
  +----------------------------------------------------------------------+
  | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
  +----------------------------------------------------------------------+
  | This source file is subject to the GENERAL PUBLIC LICENSE,           |
  | available through the world-wide-web at                              |
  | http://www.gnu.org/copyleft/gpl.html                                 |
  +----------------------------------------------------------------------+
  | Authors: Piraux Sébastien <pir@cerdecam.be>                          |
  |          Lederer Guillaume <led@cerdecam.be>                         |
  +----------------------------------------------------------------------+
*/

  $langFile = "learnPath";

  require '../../inc/claro_init_global.inc.php';

  
?>
  <html>
  <head>
    <script>
    <!-- //
    parent.tocFrame.location.href="tableOfContent.php";
    //-->
    </script> 
  </head>
  <body>
  <center>
  <br /><br /><br />
  <p>
<?php
if($_GET['op'] == 'cancel')
{
  echo $langExerciseCancelled;
}
elseif($_GET['op'] == 'finish') // exercise done
{
  echo $langExerciseDone;
}
?>
  </p>
  <center>
  </body>
  </html>
