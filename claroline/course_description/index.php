<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.6
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
/**
 * This  page show  to the user, the course description
 *
 * If ist's the admin, he can access to the editing
 *
 *
 * To  proposal here not actual in the script
 * 	- a bloc  is linked with agenda tool. To prupose big step in the course.
 *  	- a bloc  is linked with link tool.   To prupose important ressources in the course.
 *
 */

$tlabelReq = 'CLDSC___';

$langFile = 'course_description';
require '../inc/claro_init_global.inc.php';
if ( ! $_cid) claro_disp_select_course();
$is_allowedToEdit = $is_courseAdmin;
if ( ! $is_courseAllowed) claro_disp_auth_form();

include($includePath.'/lib/text.lib.php');

//@include("../lang/english/pedaSuggest.inc.php");

$nameTools = $langCourseProgram;
$htmlHeadXtra[] = '<style type="text/css"><!--
.QuestionDePlanification {  background-color: '.$colorMedium.'; background-position: left center; letter-spacing: normal; text-align: justify; text-indent: 3pt; word-spacing: normal; padding-top: 2px; padding-right: 5px; padding-bottom: 2px; padding-left: 5px}
.InfoACommuniquer { background-color: '.$colorLight.'; background-position: left center; letter-spacing: normal; text-align: justify; text-indent: 3pt; word-spacing: normal; padding-top: 2px; padding-right: 5px; padding-bottom: 2px; padding-left: 5px ; }
-->
</style>';

$TABLECOURSEDESCRIPTION = $_course['dbNameGlu']."course_description";
//stats
include($includePath."/lib/events.lib.inc.php");
event_access_tool($nameTools);

include($includePath."/claro_init_header.inc.php");

claro_disp_tool_title($nameTools);

if ($is_allowedToEdit)
{
	echo '<form method="get" action="edit.php">'
		.'<input type="submit" value="'.$langEditCourseProgram.'">'
		.'</form>';
}

$sql     =  "SELECT `id`,`title`,`content` 
             FROM `".$TABLECOURSEDESCRIPTION."` order by id";

$blocList = claro_sql_query_fetch_all($sql);

if (count($blocList))
{
    foreach($blocList as $thisBloc)
    {
        echo "<h4>".$thisBloc['title']."</h4>\n"
            . claro_parse_user_text($thisBloc['content']);
    }
}
else
{
    echo '<h4>'.$langThisCourseDescriptionIsEmpty.'</h4>';
}

include($includePath."/claro_init_footer.inc.php");
?>
