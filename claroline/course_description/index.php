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
 */

$tlabelReq = "CLDSC___";

$langFile = "course_description";
require '../inc/claro_init_global.inc.php';
if ( ! $_cid) claro_disp_select_course();
$is_allowedToEdit = $is_courseAdmin;
if ( ! $is_courseAllowed) claro_disp_auth_form();

$nameTools = $langCourseProgram;

/*
 * DB tables definition
 */

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_course_description  = $tbl_cdb_names['course_description'];

//stats
include($includePath."/lib/events.lib.inc.php");
event_access_tool($_tid, $_SESSION['_courseTool']['label']);

$sql = "SELECT `id`, `title`, `content` 
        FROM `".$tbl_course_description."` 
        ORDER BY `id`";
$blocList = claro_sql_query_fetch_all($sql);

if (!count($blocList))
{
    $msg[][] = $langThisCourseDescriptionIsEmpty;
}

//////////////////////////////
////////////OUTPUT////////////
//////////////////////////////


include($includePath."/claro_init_header.inc.php");

claro_disp_tool_title(array("mainTitle"=>$nameTools));
claro_disp_msg_arr($msg);
if ($is_allowedToEdit)
{
?>
<br>
<form method="get" action="edit.php">
<input type="submit" value="<?php echo $langEditCourseProgram ?>">
</form>


<?php
}
echo "\n\n";
if (count($blocList))
{
    foreach($blocList as $thisBloc)
    {
        echo "<h4>".$thisBloc['title']."</h4>\n"
            ."<blockquote>"
            . claro_parse_user_text($thisBloc['content'])
            ."</blockquote>"."\n";
    }
}

include($includePath."/claro_init_footer.inc.php");
?>
