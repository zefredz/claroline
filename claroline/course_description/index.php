<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.6
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
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

$tlabelReq = "CLDSC___";

$langFile = "course_description";
require '../inc/claro_init_global.inc.php';
include($includePath."/lib/text.lib.php");

$nameTools = $langCourseProgram;

/*
 * DB tables definition
 */

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_course_description  = $tbl_cdb_names['course_description'];

$is_allowedToEdit = $is_courseAdmin;
//stats
include($includePath."/lib/events.lib.inc.php");
event_access_tool($_tid, $_SESSION['_courseTool']['label']);
$sql = "SELECT `id`,`title`,`content` FROM `".$tbl_course_description."` order by id";
$desc_bloc = claro_sql_query_fetch_all($sql);

if (count($desc_bloc))
{
	
}
else
{
	$subtitle =	$langThisCourseDescriptionIsEmpty;
}

//////////////////////////////
////////////OUTPUT////////////
//////////////////////////////

include($includePath."/claro_init_header.inc.php");
if ( ! $is_courseAllowed) claro_disp_auth_form();
claro_disp_tool_title(array("mainTitle"=>$nameTools,"subTitle"=>$subtitle));

if ($is_allowedToEdit)
{
?>

<form method="get" action="edit.php">
<input type="submit" value="<?php echo $langEditCourseProgram ?>">
</form>

<?php
}

$sql     =  "SELECT `id`,`title`,`content` 
             FROM `".$tbl_course_description."` order by id";

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
