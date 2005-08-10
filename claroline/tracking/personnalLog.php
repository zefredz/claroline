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

if (!$_uid) claro_disp_auth_form();

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_courses			= $tbl_mdb_names['course'];
$tbl_link_user_courses	= $tbl_mdb_names['rel_course_user'];

include($includePath."/lib/statsUtils.lib.inc.php");

////////////// OUTPUT //////////////////////

include($includePath."/claro_init_header.inc.php");
echo claro_disp_tool_title($nameTools);

if ( $is_trackingEnabled )
{
	// display list of course of the student with links to the corresponding userLog
	$sql = "SELECT cours.code code,
			cours.intitule name,
			cours.titulaires prof

			FROM    `".$tbl_courses."`       cours,
				`".$tbl_link_user_courses."`   cours_user

			WHERE cours.code = cours_user.code_cours
			AND   cours_user.user_id = '". (int)$_uid."'";

	$resCourseListOfUser = claro_sql_query($sql);

	if(mysql_num_rows($resCourseListOfUser) > 0)
	{
		echo "\n\n".'<ul>'."\n\n";
		while ( $courseOfUser = mysql_fetch_array($resCourseListOfUser) )
		{
			echo '<li>'."\n"
				.'<a href="userLog.php?uInfo='.$_uid.'&cidReset=true&cidReq='.$courseOfUser['code'].'">'.$courseOfUser['name'].'</a><br>'."\n"
				.'<small>'.$courseOfUser['code'].' - '.$courseOfUser['prof'].'</small>'."\n"
				.'</li>'."\n";
		}
		echo "\n".'</ul>'."\n";
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
