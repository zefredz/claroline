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

require '../inc/claro_init_global.inc.php';

$interbredcrump[]= array ("url"=>"../auth/profile.php", "name"=> $langModifyProfile);
$nameTools = $langStatistics;

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_courses			= $tbl_mdb_names['course'];
$tbl_link_user_courses	= $tbl_mdb_names['rel_course_user'];



$limitOfDisplayedLogins = 25; // number of logins to display
include($includePath."/lib/statsUtils.lib.inc.php");



////////////// OUTPUT //////////////////////

include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title($nameTools);

if ( $is_trackingEnabled )
{
      // display list of course of the student with links to the corresponding userLog
      $resCourseListOfUser = claro_sql_query("SELECT cours.code code, 
                                              cours.intitule name, 
                                              cours.titulaires prof
                                              
	                                   FROM    `".$tbl_courses."`       cours,
	                                   `".$tbl_link_user_courses."`   cours_user
	                                  
	                                  WHERE cours.code = cours_user.code_cours
	                                  AND   cours_user.user_id = '".$_uid."'");
      if(mysql_num_rows($resCourseListOfUser) > 0)
      {
          echo "<ul>\n";
          while ( $courseOfUser = mysql_fetch_array($resCourseListOfUser) )
          {
              echo "<li>\n"
                        ."<a href=\"userLog.php?uInfo=".$_uid."&cidReset=true&cidReq=".$courseOfUser['code']."\">".$courseOfUser['name']."</a><br>\n"
                        ."<small>".$courseOfUser['code']." - ".$courseOfUser['prof']."</small>\n"
                        ."</li>\n\n";
          }
          echo "</ul>\n";
      }
      else
      {
          echo $langNoRegisteredCourses;
      }
}
else
{
    echo $langTrackingDisabled;
}

include($includePath."/claro_init_footer.inc.php");
?>
