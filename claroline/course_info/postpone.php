<?php  // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.6
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |          MODIFY COURSE INFO                                          |
      | Modify course settings like:										 |
      |     1. Course title													 |
      |     2. Department													 |
      |     3. Course description URL in the university web					 |
      | Course code cannot be modified, because it gives the name for the	 |
	  | course database and course web directory. Professor cannot be 		 |
	  | changed either as it determines who is allowed to modify the course. |
      +----------------------------------------------------------------------+
 */
//$interbredcrump[]= array ("url"=>"index.php", "name"=> $langAdmin);

require '../inc/claro_init_global.inc.php';
if ( ! $_cid) claro_disp_select_course();



@include($includePath."/lib/debug.lib.inc.php");
//@include($includePath."/conf/postpone.conf.php");

$nameTools = "Postpone";
//$TBL_AGENDA 		= $_course['dbNameGlu']."agenda";

/*
 * DB tables definition
 */

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_course = $tbl_mdb_names['course'];

$is_allowedToEdit 			= $is_courseAdmin;
$currentCourseID 			= $_course['sysCode'];
$currentCourseRepository 	= $_course["path"];


$sqlCourseExtention 			= "SELECT lastVisit, lastEdit, creationDate, expirationDate FROM `".$tbl_course."` WHERE code = '".$_cid."'";
$resultCourseExtention 			= claro_sql_query($sqlCourseExtention);
$currentCourseExtentionData 	= mysql_fetch_array($resultCourseExtention);
$currentCourseLastVisit 		= $currentCourseExtentionData["lastVisit"];
$currentCourseLastEdit			= $currentCourseExtentionData["lastEdit"];
$currentCourseCreationDate 		= $currentCourseExtentionData["creationDate"];
$currentCourseExpirationDate	= $currentCourseExtentionData["expirationDate"];
// HERE YOU CAN EDIT YOUR RULES TO EXTEND THE LIFE OF COURSE

// $newCourseExpirationDate	= now() + $extendDelay
include($includePath."/claro_init_header.inc.php");

claro_disp_tool_title("mainTitle"=>$nameTools,"subTitle"=>$langSubTitle);
?>

this script  would be  called  by  
	professor, 
	or administrator, 
	or other  script 
to give more time to a course before expiration



<?php
include($includePath."/claro_init_footer.inc.php");
?>
