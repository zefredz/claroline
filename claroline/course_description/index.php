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
//@include("../lang/english/pedaSuggest.inc.php");
$nameTools = $langCourseProgram;
// $interbredcrump[]= array ("url"=>"index.php", "name"=> $langCourseProgram);
$htmlHeadXtra[] = "<style type=\"text/css\"><!--
.QuestionDePlanification {  background-color: ".$color2."; background-position: left center; letter-spacing: normal; text-align: justify; text-indent: 3pt; word-spacing: normal; padding-top: 2px; padding-right: 5px; padding-bottom: 2px; padding-left: 5px}
.InfoACommuniquer { background-color: ".$color1."; background-position: left center; letter-spacing: normal; text-align: justify; text-indent: 3pt; word-spacing: normal; padding-top: 2px; padding-right: 5px; padding-bottom: 2px; padding-left: 5px ; }
-->
</style>";
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

$sql = "SELECT `id`,`title`,`content` FROM `".$tbl_course_description."` order by id";
$res = claro_sql_query($sql);
if ( mysql_num_rows($res) >0 )
{
	echo "
<hr noshade size=\"1\">";
	while ($bloc = mysql_fetch_array($res))
	{ 
		echo "
<h4>
	".$bloc["title"]."
</h4>
".make_clickable(claro_parse_user_text($bloc["content"]));
	}
}

include($includePath."/claro_init_footer.inc.php");
?>
