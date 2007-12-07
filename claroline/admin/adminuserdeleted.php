<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

/* This script is used to delete a user from the platform in the admin 
   tool from the page to visualize the user profile (adminprofile.php)
   and display a confirmation message to the admin.
*/

$cidReset = TRUE;$gidReset = TRUE;$tidReset = TRUE;

require '../inc/claro_init_global.inc.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die($langNotAllowed);

include $includePath.'/lib/admin.lib.inc.php';
include $includePath.'/lib/user.lib.php';
include $includePath.'/conf/user_profile.conf.php'; // find this file to modify values.

$nameTools=$langUserSettings;

$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministration);

//declare needed tables

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_course           = $tbl_mdb_names['course'           ];
$tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'  ];
$tbl_user             = $tbl_mdb_names['user'             ];
$tbl_admin            = $tbl_mdb_names['admin'            ];
$tbl_course_user = $tbl_rel_course_user;

//------------------------------------
// Execute COMMAND section
//------------------------------------

if (isset($_REQUEST['cmd']))
     $cmd = $_REQUEST['cmd'];
else $cmd = null;

if ( isset($cmd) && $is_platformAdmin )
{
    if ( $cmd=='delete' )
    {
        $user_id = $_REQUEST['uidToEdit'];
        
        if ( user_delete($user_id) )
        {
            $dialogBox = $langUserDelete;
        }
        else
        {
            $dialogBox = $langNotUnregYourself;
        }
    }
}

//------------------------------------
// DISPLAY
//------------------------------------

include($includePath.'/claro_init_header.inc.php');

// Display tool title

echo claro_disp_tool_title($langDeleteUser);

//Display Forms or dialog box(if needed)

if ( isset($dialogBox) )
{
    echo claro_disp_message_box($dialogBox);
}

// display TOOL links :

echo "<a class=\"claroCmd\" href=\"index.php\" >".$langBackToAdmin."</a> | ";
echo "<a class=\"claroCmd\" href=\"adminusers.php\" >".$langBackToUserList."</a>";


// display footer
include($includePath."/claro_init_footer.inc.php");

?>
