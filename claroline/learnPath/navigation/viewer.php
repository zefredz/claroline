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
  
  // if there is an auth information missing redirect to the first page of lp tool 
  // this page will do the necessary to auth the user, 
  // when leaving a course all the LP sessions infos are cleared so we use this trick to avoid other errors
  if ( ! $_cid) header("Location:../learningPathList.php");
  if ( ! $is_courseAllowed) header("Location:../learningPathList.php");
  
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
  
  // set charset as claro_header should do but we cannot include it here
  header('Content-Type: text/html; charset='. $charset);
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
  
  	<?php
		echo $langBrowserCannotSeeFrames."<br />"
			."<a href=\"../module.php\">".$langBack."</a>";
	?>
  
   </body>
</noframes>
</html>
