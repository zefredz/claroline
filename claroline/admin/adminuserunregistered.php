<?php
// $Id$
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

$cidReset = TRUE;$gidReset = TRUE;$tidReset = TRUE;
require '../inc/claro_init_global.inc.php';
include $includePath."/lib/admin.lib.inc.php";
include $includePath.'/conf/user_profile.conf.php'; // find this file to modify values.


//SECURITY CHECK
if (!$is_platformAdmin) claro_disp_auth_form();

$nameTools=$langUserSettings;

$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministration);

//declare needed tables
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_admin           = $tbl_mdb_names['admin'           ];
$tbl_course           = $tbl_mdb_names['course'           ];
$tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'  ];
$tbl_course_nodes     = $tbl_mdb_names['category'         ];
$tbl_user             = $tbl_mdb_names['user'             ];
//$tbl_class            = $tbl_mdb_names['class'            ];
//$tbl_rel_class_user   = $tbl_mdb_names['rel_class_user'   ];


// see which user we are working with ...

$user_id = $_REQUEST['uidToEdit'];

//------------------------------------
// Execute COMMAND section
//------------------------------------

if (isset($cmd) && $is_platformAdmin)
{
    if ($cmd=="UnReg")
    {
        remove_user_from_course($user_id, $cidToEdit);
        $dialogBox = $langUserUnregisteredFromCourse;
    }

}

//------------------------------------
// DISPLAY
//------------------------------------


include($includePath.'/claro_init_header.inc.php');

// Display tool title

claro_disp_tool_title($langUserUnregistered);

//Display Forms or dialog box(if needed)

if($dialogBox)
  {
    claro_disp_message_box($dialogBox);
  }


// display TOOL links :

echo "<a class=\"claroCmd\" href=\"index.php\">".$langBackToAdmin."</a> | ";
echo "<a class=\"claroCmd\" href=\"adminusercourses.php?uidToEdit=".$user_id."\">".$langBackToCourseList."</a>";

// display footer

include($includePath."/claro_init_footer.inc.php");
?>
