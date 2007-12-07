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

  $nameTools = $langToolName;
  if(!empty($nameTools))
  {
    $titlePage .= $nameTools.' - ';
  }
  
  if(!empty($_course['officialCode']))
  {
    $titlePage .= $_course['officialCode'].' - ';
  }
  $titlePage .= $siteName;
  
?>

<html>

  <head>
    <title><?php echo $titlePage; ?></title>
  </head>
<?PHP
if (!isset($noFrames))
{
?>
    <frameset border="0" rows="150,*,70" frameborder="no" />
        <frame src="topModule.php" name="headerFrame" />
        <frame src="startModule.php" name="mainFrame" />         
        <frame src="bottomModule.php" name="bottomFrame" />
    </frameset>
<?PHP
}
else
{
?>
    <frameset cols="*" border="0">
        <frame src="startModule.php" name="mainFrame" />    
    </frameset>
<?PHP
}
?>
  <noframes>
  <body>
  
  <?php  echo $langBrowserCannotSeeFrames;  ?>
  
   </body>
</noframes>
</html>
