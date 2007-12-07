<?php // $Id$
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
include $includePath."/lib/user.lib.php";
include $includePath.'/conf/user_profile.conf.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die($langNotAllowed);

$nameTools=$langUserSettings;
$dialogBox = '';

$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministration);

//declare needed tables
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_admin           = $tbl_mdb_names['admin'           ];
$tbl_course           = $tbl_mdb_names['course'           ];
$tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'  ];
$tbl_course_nodes     = $tbl_mdb_names['category'         ];
$tbl_user             = $tbl_mdb_names['user'             ];

// see which user we are working with ...

$user_id = $_REQUEST['uidToEdit'];

//------------------------------------
// Execute COMMAND section
//------------------------------------

if ( isset($_REQUEST['cmd'] ) && $is_platformAdmin )
{
    if ( $_REQUEST['cmd'] == 'UnReg' )
    {
        if ( user_remove_from_course($user_id, $_REQUEST['cidToEdit'],true) )
        {
            $dialogBox .= $langUserUnsubscribed;
        }
        else
        {
            switch ( claro_failure::get_last_failure() )
            {
                case 'cannot_unsubscribe_the_last_course_manager' :
                    $dialogBox .= $langCannotUnsubscribeLastCourseManager;
                    break;
                case 'course_manager_cannot_unsubscribe_himself' :
                    $dialogBox .= $langCourseManagerCannotUnsubscribeHimself;
                    break;
                default :       
            }       
        }
    }
}

//------------------------------------
// DISPLAY
//------------------------------------

// Display header

include($includePath.'/claro_init_header.inc.php');

// Display tool title

echo claro_disp_tool_title($langUserUnregistered);

// Display Forms or dialog box(if needed)

if ( !empty($dialogBox) )
{
    echo claro_disp_message_box($dialogBox);
}

// Display TOOL links :

echo "<a class=\"claroCmd\" href=\"index.php\">".$langBackToAdmin."</a> | ";
echo "<a class=\"claroCmd\" href=\"adminusercourses.php?uidToEdit=".$user_id."\">".$langBackToCourseList."</a>";

// Display footer

include($includePath."/claro_init_footer.inc.php");

?>
