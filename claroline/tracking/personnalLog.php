<?php # $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.0 $Revision$                             |
      +----------------------------------------------------------------------+
      | Authors:               |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      |          Sebastien Piraux  <piraux_seb@hotmail.com>
      +----------------------------------------------------------------------+
 */

$langFile = "tracking";
require '../inc/claro_init_global.inc.php';

$interbredcrump[]= array ("url"=>"../auth/profile.php", "name"=> $langModifyProfile);
$nameTools = $langToolName;

$htmlHeadXtra[] = "<style type=\"text/css\">
<!--
.secLine {background-color : #E6E6E6;}
.content {padding-left : 15px; padding-right : 15px;}
.specialLink{color : #0000FF;}
-->
</style>
<STYLE media='print' type='text/css'>
<!--
TD {border-bottom: thin dashed Gray;}
-->
</STYLE>";

// regroup table names for maintenance purpose
$tbl_courses			= $mainDbName."`.`cours";
$tbl_link_user_courses	= $mainDbName."`.`cours_user";



$limitOfDisplayedLogins = 25; // number of logins to display
include($includePath."/lib/statsUtils.lib.inc.php");



////////////// OUTPUT //////////////////////

include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title($nameTools);

if ( $is_trackingEnabled )
{
      // display list of course of the student with links to the corresponding userLog
      $resCourseListOfUser = mysql_query("SELECT cours.code code, 
                                              cours.intitule name, 
                                              cours.titulaires prof
                                              
	                                   FROM    `".$tbl_courses."`       cours,
	                                   `".$tbl_link_user_courses."`   cours_user
	                                  
	                                  WHERE cours.code = cours_user.code_cours
	                                  AND   cours_user.user_id = '".$_uid."'");
      echo "<ul>";
      while ( $courseOfUser = mysql_fetch_array($resCourseListOfUser) )
      {
          ?>
            
                <li><a href="userLog.php?uInfo=<?= $_uid; ?>&cidReset=true&cidReq=<?= $courseOfUser['code']; ?>"><?= $courseOfUser['name'];?></a><br>
                  <small><?= $courseOfUser['code']; ?> - <?= $courseOfUser['prof']; ?></small> 
                </li> 
          <?
              
      }
      echo "</ul>";
}
else
{
    echo $langTrackingDisabled;
}

include($includePath."/claro_init_footer.inc.php");
?>
