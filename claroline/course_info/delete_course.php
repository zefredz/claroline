<?php # $Id$

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

$_tid="deletecourse";
$langFile = 'course_info';

require '../inc/claro_init_global.inc.php';

// in case of admin access (from admin tool) to the script, we must determine which course we are wroking with

if (isset($cidToEdit) && ($is_platformAdmin))
{
    $current_cid       = $cidToEdit;
    $currentCourseId   = $cidToEdit;
    $cidReq            = $cidToEdit;
    $isAllowedToDelete = true;
    $addToURL          = "&cidToEdit=".$cidToEdit;
    $addToURL         .="&cfrom=".$cfrom;
}
else
{
    $current_cid = $_course['sysCode'];
}

//check user right

$isAllowedToDelete = ($is_courseAdmin || $is_platformAdmin);

//used tables
/*
 * DB tables definition
 */

//$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names     = claro_sql_get_main_tbl();
$tbl_course        = $tbl_mdb_names['course'           ];
$tbl_relCourseUser = $tbl_mdb_names['rel_course_user'  ];

//find needed info in db

$sql = "SELECT * FROM `".$tbl_course."` WHERE code = '".$current_cid."'";
list($course_to_delete) = claro_sql_query_fetch_all($sql);

$currentCourseDbName 	= $course_to_delete['dbName'];
$currentCourseDbNameGlu = $course_to_delete['dbNameGlu'];
$currentCoursePath 		= $course_to_delete['path'];
$currentCourseCode 		= $course_to_delete['fake_code'];
$currentCourseName 		= $course_to_delete['intitule'];

$nameTools = $langDelCourse;

$interbredcrump[]=array("url" => "infocours.php?".$addToURL,"name" => $langModifInfo);

include($includePath."/claro_init_header.inc.php");
include($includePath."/lib/fileManage.lib.php");
include($includePath."/lib/events.lib.inc.php");
include($includePath."/lib/admin.lib.inc.php");

// display tool title

claro_disp_tool_title($nameTools);

if($isAllowedToDelete)
{
	if($delete)
	{
          // DO DELETE

          delete_course($current_cid);

          // DELETE CONFIRMATION MESSAGE

        event_default("DELETION COURSE",array ("courseName"=>$currentCourseName, "_uid"=> $_uid));
        echo     "<p>"
                .$langCourse." &quot;".$currentCourseName."&quot; "
                ."(",$currentCourseCode,") "
                .$langHasDel."</p>";

        echo     "<p>"
                ."<a href=\"../../index.php\">"
                .$langBackHome." ". $siteName
                ."</a>"
                ;
        if (isset($cidToEdit))    //we can suppose that script is accessed from admin tool in this case
        {
            echo " | "
                ."<a href=\"../admin/index.php\">"
                .$langBackToAdminPage." </a>"
                ;
        }
        echo "</p>";
	}					// end if $delete
	else
	{
		// ASK DELETE CONFIRMATION TO THE USER

		echo	 "<p>"

				."<font color=\"#CC0000\">"
				.$langByDel." &quot;".$currentCourseName,"&quot; "
				."(".$currentCourseCode.") ?"
				."</font>"
				."</p>"

				."<p>"
				."<font color=\"#CC0000\">";

		echo "<a href=\"".$PHP_SELF."?delete=yes".$addToURL."\">"
			.$langY
			."</a>";


		echo "&nbsp;|&nbsp;";

        echo "<a href=\"infocours.php?".$addToURL."\">"
				.$langN
				."</a>";

		echo "</font>"
			."</p>";

	}		// end else if $delete
}			// end if $isAllowedToDelete
else
{
	echo $langNotAllowed;
}

include($includePath."/claro_init_footer.inc.php");
?>