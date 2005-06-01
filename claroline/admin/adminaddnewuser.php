<?php # $Id$
//----------------------------------------------------------------------
// CLAROLINE 160
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

/*=====================================================================
 Init Section
 =====================================================================*/ 

$cidReset = TRUE;
$gidReset = TRUE;
$tidReset = TRUE;

require '../inc/claro_init_global.inc.php';

claro_unquote_gpc();

// Security Check
$is_allowedToAdmin     = $is_platformAdmin;
if (!$is_allowedToAdmin) claro_disp_auth_form();

// Include library
include($includePath.'/conf/user_profile.conf.php');
include($includePath.'/lib/debug.lib.inc.php');
include($includePath.'/lib/user.lib.php');
include($includePath.'/lib/auth.lib.inc.php');
include($includePath.'/lib/claro_mail.lib.inc.php');

// Initialise variables
$nameTools = $langAddUser;
$error = false;
$message = '';

// DB tables definition
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user = $tbl_mdb_names['user'];

/*=====================================================================
  Main Section
 =====================================================================*/ 

// Initialise field variable from subscription form 
$user_data = user_initialise();

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = '';

if ( $cmd == 'registration' )
{
    // get params from the form

    if ( isset($_REQUEST['lastname']) )      $user_data['lastname'] = strip_tags(trim($_REQUEST['lastname'])) ;
    if ( isset($_REQUEST['firstname']) )     $user_data['firstname']  = strip_tags(trim($_REQUEST['firstname'])) ;
    if ( isset($_REQUEST['officialCode']) )  $user_data['officialCode']  = strip_tags(trim($_REQUEST['officialCode'])) ;
    if ( isset($_REQUEST['username']) )      $user_data['username']  = strip_tags(trim($_REQUEST['username']));
    if ( isset($_REQUEST['password']) )      $user_data['password']  = trim($_REQUEST['password']);
    if ( isset($_REQUEST['password_conf']) ) $user_data['password_conf']  = trim($_REQUEST['password_conf']);
    if ( isset($_REQUEST['email']) )         $user_data['email']  = strip_tags(trim($_REQUEST['email'])) ;
    if ( isset($_REQUEST['phone']) )         $user_data['phone']  = trim($_REQUEST['phone']);
    if ( isset($_REQUEST['status']) )        $user_data['status']  = (int) $_REQUEST['status'];

    // check if there are no empty fields

    $regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";

    if (  empty($user_data['lastname'] )       || empty($user_data['firstname'] ) 
        || empty($user_data['password_conf'] ) || empty($user_data['password'] )
        || empty($user_data['username'] )      || (empty($user_data['email'] ) && !$userMailCanBeEmpty) )
    {
        $error = true;
        $message .= '<p>' . $langEmptyFields . '</p>' . "\n";
    }
    
    // check if the two password are identical
    elseif ( $user_data['password_conf']  != $user_data['password']  )
    {
        $error = true;
        $message .= '<p>' . $langPassTwice . '</p>' . "\n";
    }

    // check if password isn't too easy
    elseif ( $user_data['password'] 
             && SECURE_PASSWORD_REQUIRED
             && ! is_password_secure_enough($user_data['password'],
                  array( $user_data['username'] , 
                         $user_data['officialCode'] , 
                         $user_data['lastname'] , 
                         $user_data['firstname'] , 
                         $user_data['email'] )) )
    {
        $error = true;
        $message .= '<p>' . $langPassTooEasy . ' <code>' . substr(md5(date('Bis').$_SERVER['HTTP_REFERER']),0,8) . '</code></p>' . "\n";
    }

    // check email address validity
    elseif ( !empty($user_data['email'] ) && ! eregi($regexp,$user_data['email'] ) )
    {
        $error = true;
        $message .= '<p>' . $langEmailWrong . '</p>' . "\n" ;
    }

    // check if the username is already owned by another user
    else
    {
        $sql = 'SELECT COUNT(*) `loginCount`
                FROM `'.$tbl_user.'` 
                WHERE username="' . addslashes($user_data['username'] ) . '"';

        list($result) = claro_sql_query_fetch_all($sql);

        if ( $result['loginCount'] > 0 )
        {
            $error = true;
            $message .= '<p>' . $langUserTaken . '</p>' . "\n";
        }

    }

    if ( $error == false )
    {
        // register the new user in the claroline platform
        $inserted_uid = user_insert($user_data);
        
        // send a mail to the user
        user_send_registration_mail($inserted_uid,$user_data);
    }
}

/*=====================================================================
  Display Section
 =====================================================================*/ 

$interbredcrump[] = array ("url"=>$rootAdminWeb, "name"=> $langAdministration);
$noQUERY_STRING   = TRUE;

// Display Header
include($includePath."/claro_init_header.inc.php");

// Display title
claro_disp_tool_title( array('mainTitle'=>$nameTools ) );

if ( $cmd == 'registration' && $error == false )
{
    echo '<p>' . $langUserCreated . '</p>'
        . '<ul>'
        . '<li><a class="claroCmd" href="../auth/courses.php?cmd=rqReg&uidToEdit=' . $inserted_uid . '&category=&fromAdmin=settings">' . $langRegisterTheNewUser . '</a></li>'
        . '<li><a class="claroCmd" href="adminprofile.php?uidToEdit=' . $inserted_uid . '&category="> ' . $langGoToUserSettings . '</a></li>'
        . '<li><a class="claroCmd" href="adminaddnewuser.php"> ' . $langCreateAnotherUser . ' </a></li>'
        . '<li><a class="claroCmd" href="index.php"> ' . $langBackToAdmin . ' </a></li>'
        . '</ul>';
}
else
{
    if ( $error ) 
    {
        claro_disp_message_box($message);
    }
    
    echo $langAddUserOneByOne;

    user_display_form_admin_add_new_user($user_data);

}

// Display footer
include($includePath."/claro_init_footer.inc.php");
?>
