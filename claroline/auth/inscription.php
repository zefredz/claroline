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

// Include claro_init_global
require '../inc/claro_init_global.inc.php';

claro_unquote_gpc();

// Redirect before first output
if( ! isset($allowSelfReg) || $allowSelfReg == FALSE)
{
    header("Location: ".$rootWeb);
    exit;
}

// include profile library
include($includePath.'/conf/user_profile.conf.php');
include($includePath.'/lib/auth.lib.inc.php');
include($includePath.'/lib/claro_mail.lib.inc.php');
include($includePath.'/lib/events.lib.inc.php');

// database tables definition
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user  = $tbl_mdb_names['user'];

// Initialise variables

$error = false;
$message = '';

DEFINE('CONFVAL_ASK_FOR_OFFICIAL_CODE',TRUE);
if ( !isset($userMailCanBeEmpty) ) $userMailCanBeEmpty = true;
if ( !isset($userPasswordCrypted) ) $userPasswordCrypted = false;

// Initialise field variable from subscription form 
    
$lastname = '';
$firstname = '';
$officialCode = '';
$username = '';
$password = '';
$password_conf = '';
$email = '';
$phone = '';
$status = STUDENT;

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = '';

$display_status_selector = (bool) ($is_platformAdmin OR $allowSelfRegProf);

// Main Section

if ( $cmd == 'registration' )
{
    $regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";

    // get params from the form
    if ( isset($_REQUEST['lastname']) )      $lastname = strip_tags(trim($_REQUEST['lastname'])) ;
    if ( isset($_REQUEST['firstname']) )     $firstname = strip_tags(trim($_REQUEST['firstname'])) ;
    if ( isset($_REQUEST['officialCode']) )  $officialCode = strip_tags(trim($_REQUEST['officialCode'])) ;
    if ( isset($_REQUEST['username']) )      $username = strip_tags(trim($_REQUEST['username']));
    if ( isset($_REQUEST['password']) )      $password = trim($_REQUEST['password']);
    if ( isset($_REQUEST['password_conf']) ) $password_conf = trim($_REQUEST['password_conf']);
    if ( isset($_REQUEST['email']) )         $email = strip_tags(trim($_REQUEST['email'])) ;
    if ( isset($_REQUEST['phone']) )         $phone = trim($_REQUEST['phone']);
    if ( isset($_REQUEST['status']) )        $status = (int) $_REQUEST['status'];

    // check if there are no empty fields
    if (  empty($lastname)       || empty($firstname) 
        || empty($password_conf) || empty($password)
        || empty($username)     || (empty($email) && !$userMailCanBeEmpty) )
    {
        $error = true;
        $message .= '<p>' . $langEmptyFields . '</p>' . "\n";
    }
    
    // check if the two password are identical
    elseif ( $password_conf != $password )
    {
        $error = true;
        $message .= '<p>' . $langPassTwice . '</p>' . "\n";
    }

    // check if password isn't too easy
    elseif ( $password
             && SECURE_PASSWORD_REQUIRED
             && ! is_password_secure_enough($password,array($username, $officialCode, $lastname, $firstname, $email)) )
    {
        $error = true;
        $message .= '<p>' . $langPassTooEasy . ' <code>' . substr(md5(date('Bis').$_SERVER['HTTP_REFERER']),0,8) . '</code></p>' . "\n";
    }

    // check email address validity
    elseif ( !empty($email) && ! eregi($regexp,$email) )
    {
        $error = true;
        $message .= '<p>' . $langEmailWrong . '</p>' . "\n" ;
    }

    // check if the username is already owned by anotehr user
    else
    {
        $sql = 'SELECT COUNT(*) `loginCount`
                FROM `'.$tbl_user.'` 
                WHERE username="' . addslashes($username) . '"';

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
        
        $password = $userPasswordCrypted?md5($password):$password;

        $sql = "INSERT INTO `".$tbl_user."`
                SET `nom`          = '". addslashes($lastname) ."' ,
                    `prenom`       = '". addslashes($firstname) ."',
                    `username`     = '". addslashes($username) ."',
                    `password`     = '". addslashes($password) ."',
                    `email`        = '". addslashes($email) ."',
                    `statut`       = '". (int) $status ."',
                    `officialCode` = '". addslashes($officialCode) ."',
                    `phoneNumber`  = '". addslashes($phone) ."'";

        $_uid = claro_sql_query_insert_id($sql);

        if ( $_uid )
        {
            // add value in session
            $_user['firstName']     = $firstname;
            $_user['lastName' ]     = $lastname;
            $_user['mail'     ]     = $email;
            $_user['lastLogin']     = time() - (24 * 60 * 60); // DATE_SUB(CURDATE(), INTERVAL 1 DAY)
            $is_allowedCreateCourse = ($status == 1) ? TRUE : FALSE ;

            $_SESSION['_uid'] = $_uid;
            $_SESSION['_user'] = $_user;
            $_SESSION['is_allowedCreateCourse'] = $is_allowedCreateCourse;
            
            // track user login
            event_login();
    
            // last user login date is now
            $user_last_login_datetime = 0; // used as a unix timestamp it will correspond to : 1 1 1970
            $_SESSION['user_last_login_datetime'] = $user_last_login_datetime;
    
            // send info to user by email 

            if( !empty($email) )
            {
                $emailSubject  = '[' . $siteName . '] ' . $langYourReg ;

                // The body can be as long as you wish, and any combination of text and variables

                $emailBody = $langDear . ' ' . $firstname . ' ' . $lastname . ',' . "\n"
                            . $langYouAreReg . ' ' . $siteName . ' ' . $langSettings . ' ' . $username . "\n"
                            . $langPassword . ' : ' . $password . "\n"
                            . $langAddress . ' ' . $siteName . ' ' . $langIs . ' : ' . $rootWeb . "\n"
                            . $langProblem . "\n"
                            . $langFormula . ',' . "\n"
                            . $administrator_name . "\n"
                            . $langManager . ' ' . $siteName . "\n"
                            . 'T. ' . $administrator_phone . "\n"
                            . $langEmail . ' : ' . $administrator_email . "\n";

                claro_mail_user($_uid, $emailBody, $emailSubject);
            }
        
        } // if _uid

    } // end register user    

}

// Display Section

$interbredcrump[]= array ("url"=>"inscription.php", "name"=> $langRegistration);

// Display Header
include($includePath."/claro_init_header.inc.php");

// Display Title
claro_disp_tool_title($langRegistration);

if ( $cmd == 'registration' && $error == false )
{
        // registration succeeded

        printf($langMessageSubscribeDone_p_firstname_lastname, $firstname, $lastname);

        if ( $is_allowedCreateCourse )
        {
            echo '<p>' . $langNowGoCreateYourCourse . '</p>' . "\n";
        }
        else
        {
            echo '<p>' . $langNowGoChooseYourCourses . '</p>' . "\n";
        }

        echo '<form action="../../index.php?cidReset=1" >'
            . '<input type="submit" name="next" value="' . $langNext . '" validationmsg=" ' . $langNext . ' ">' . "\n"
            . '</form>'."\n" ;
}
else
{
    //  if registration failed display error message
    if ( $error ) 
    {
        claro_disp_message_box($message);
    }

    // display registration form
    echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">' . "\n"
        . '<input type="hidden" name="cmd" value="registration" />' . "\n"
        . '<input type="hidden" name="claroFormId" value="' . uniqid(rand()) . '" />' . "\n"
    
        . '<table cellpadding="3" cellspacing="0" border="0">' . "\n"
        . ' <tr>' . "\n"
        . '  <td align="right"><label for="lastname">' . $langLastname . '&nbsp;:</label></td>' . "\n"
        . '  <td><input type="text" size="40" name="lastname" id="lastname" value="' . htmlspecialchars($lastname) . '" /></td>' . "\n"
        . ' </tr>' . "\n"

        . ' <tr>' . "\n"
        . '  <td align="right"><label for="firstname">' . $langFirstname . '&nbsp;:</label></td>' . "\n"
        . '  <td><input type="text" size="40" id="firstname" name="firstname" value="' . htmlspecialchars($firstname) . '" /></td>' . "\n"
        . ' </tr>' . "\n" ;

    if ( CONFVAL_ASK_FOR_OFFICIAL_CODE )
    {
        echo ' <tr>'  . "\n"
            . '  <td align="right"><label for="officialCode">' . $langOfficialCode . '&nbsp;:</label></td>'  . "\n"
            . '  <td><input type="text" size="40" id="offcialCode" name="officialCode" value="' . htmlspecialchars($officialCode) . '" /></td>' . "\n"
            . ' </tr>' . "\n";
    }

    echo ' <tr>' . "\n"
        . '  <td>&nbsp;</td>' . "\n"
        . '  <td>&nbsp;</td>' . "\n"
        . ' </tr>' . "\n";

    echo ' <tr>' . "\n"
        . '  <td align="right"><label for="username">' . $langUserName . '&nbsp;:</label></td>' . "\n"
        . '  <td><input type="text" size="40" id="username" name="username" value="' . htmlspecialchars($username) . '" /></td>' . "\n"
        . ' </tr>' . "\n"

        . ' <tr>'  . "\n"
        . '     <td align="right"><label for="password">' . $langPassword . '&nbsp;:</label></td>' . "\n"
        . '  <td><input type="password" size="40" id="password" name="password" /></td>' . "\n"
        . '    </tr>' . "\n"

        . ' <tr>' . "\n"
        . '     <td align="right"><label for="password_conf">' . $langPassword . '&nbsp;:<br>' . "\n" . "\n"
        . ' <small>(' . $langConfirmation . ')</small></label></td>' . "\n"
        . '  <td><input type="password" size="40" id="password_conf" name="password_conf" /></td>' . "\n"
        . ' </tr>' . "\n";
    
    echo ' <tr>' . "\n"
        . '  <td>&nbsp;</td>' . "\n"
        . '  <td>&nbsp;</td>' . "\n"
        . ' </tr>' . "\n";


    echo ' <tr>' . "\n"
        . '  <td align="right"><label for="email">' . $langEmail . '&nbsp;:</label></td>' . "\n"
        . '  <td><input type="text" size="40" id="email" name="email" value="' . htmlspecialchars($email) . '" /></td>' . "\n"
        . ' </tr>' . "\n"

        . ' <tr>' . "\n"
        . '  <td align="right"><label for="phone">' . $langPhone . '&nbsp;:</label></td>' . "\n"
        . '  <td><input type="text" size="40" id="phone" name="phone" value="' . htmlspecialchars($phone) . '" /></td>' . "\n"
        . ' </tr>' . "\n";

    // Deactivate Teacher Self-registration if $allowSelfRegProf=FALSE

    if ( $display_status_selector )
    {
        echo ' <tr>' . "\n"
            . '  <td align="right"><label for="status">' . $langAction . '&nbsp;:</label></td>' . "\n"
            . '  <td>' . "\n"
            . '<select id="status" name="status">'
            . '    <option value="' . STUDENT . '">' . $langRegStudent . '</option>'
            . '    <option value="' . COURSEMANAGER . '" ' . ($status == COURSEMANAGER ? 'selected="selected"' : '') . '>' . $langRegAdmin . '</option>'
            . '</select>'
            . '  </td>' . "\n"
            . ' </tr>' . "\n";
    }

    echo ' <tr>' . "\n"
        . '  <td>&nbsp;</td>' . "\n"
        . '     <td><input type="submit" value="' . $langRegister . '" /></td>' . "\n"
        . ' </tr>' . "\n"

        . '</table>' . "\n"

        . '</form>' . "\n";
}

// display footer
include ("../inc/claro_init_footer.inc.php");

?>
