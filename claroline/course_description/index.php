<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.2 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
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


include($includePath."/claro_init_header.inc.php");

if ( ! $is_courseAllowed) die ("<center>Not allowed</center>");

//stats
@include($includePath."/lib/events.lib.inc.php");
event_access_tool($nameTools);

$TABLECOURSEDESCRIPTION = $_course['dbNameGlu']."course_description";
$is_allowedToEdit = $is_courseAdmin;

claro_disp_tool_title($nameTools);

if ($is_allowedToEdit)
{
	echo	"<form method=\"get\" action=\"edit.php\">",
			"<input type=\"submit\" value=\"",$langEditCourseProgram,"\">",
			"</form>";
}

$sql = "SELECT `id`,`title`,`content` FROM `".$TABLECOURSEDESCRIPTION."` order by id";
$res = mysql_query($sql);
if ( mysql_num_rows($res) >0 )
{
	echo "
<hr noshade size=\"1\">";
	while ($bloc = mysql_fetch_array($res))
	{ 
		echo "
<H4>
	".$bloc["title"]."
</H4>
".make_clickable(claro_parse_user_text($bloc["content"]));
	}
}
else
{
	echo "
<br>
<H4>
	$langThisCourseDescriptionIsEmpty
</h4>";
}

include($includePath."/claro_init_footer.inc.php");
?>
