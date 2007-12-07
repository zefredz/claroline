<?php // $Id$
/**
 * @version  CLAROLINE version 1.6
 *
 * @copyright (c) 2001, 2005 Universite catholique de Louvain (UCL)
 *
 * @license GENERAL PUBLIC LICENSE
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 *
 * @package CLLNP
 *
 */
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
  </center>
 </body>
</html>