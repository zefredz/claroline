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

/*=====================================================================
 Init Section
 =====================================================================*/ 

$tlabelReq = 'CLUSR___';

require '../inc/claro_init_global.inc.php';

claro_unquote_gpc();

// Security check
if ( ! ($is_courseAdmin || $is_platformAdmin) ) claro_disp_auth_form();

// include configuration file
include($includePath."/conf/user_profile.conf.php");

// include libraries
include($includePath."/lib/debug.lib.inc.php");
include($includePath.'/lib/user.lib.php');
include($includePath.'/lib/claro_mail.lib.inc.php');

// Initialise variables
$nameTools        = $langAddAU;
$interbredcrump[] = array ('url'=>'user.php', 'name'=> $langUsers);

$messageList = array();

$platformRegSucceed = false;
$courseRegSucceed = false;

/*=====================================================================
 Main Section
 =====================================================================*/ 

// Initialise field variable from subscription form 
$user_data = user_initialise();
$user_data['is_coursemanager'] = STUDENT;
$user_data['is_tutor'] = 0;

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = '';

if ( !empty($cmd) )
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
    
    if ( isset($_REQUEST['is_coursemanager'])) $user_data['is_coursemanager'] = (int) $_REQUEST['is_coursemanager'];
    if ( isset($_REQUEST['is_tutor']))         $user_data['is_tutor'] = (int) $_REQUEST['is_tutor'];
}

switch ( $cmd )
{
    case 'registration':

        // validate forum params
        $messageList = user_validate_form_registration($user_data);

        if ( count($messageList) == 0 )
        {
            // register the new user in the claroline platform
            $user_id = user_insert($user_data);
        
            if ( $user_id ) $platformRegSucceed = true;
            
            // add user to course
            if ( user_add_to_course($user_id, $_cid, true) ) 
            {
                // update course manager and tutor status
                user_update_course_manager_status($user_id, $_cid, $user_data['is_coursemanager']);
                user_update_course_tutor_status($user_id, $_cid, $user_data['is_tutor']);
                $courseRegSucceed = true;
            }
        }
        else
        {
            // user validate form return error messages
            $error = true;
        }

    case 'search':
        // search on username, official_code, ...

        // build result box with subscribe button        

        break;

    case 'subscribe_to_course':

        if ( isset($_REQUEST['user_id']) ) 
        {
            $user_id = $_REQUEST['user_id'];

            // add user to course
            user_add_to_course($user_id, $_cid, true);

            // get user info
            $user_data = user_get_data($user_id);

            $courseRegSucceed = true;        
        }
        else
        {
            $error = true;
        }
        break;

    default:
        // do nothing
        break;

} // end switch cmd

          
// Send mail notification

if ( $platformRegSucceed || $courseRegSucceed ) // why course Reg Failed ?
{
    // Mail to 
    $emailto       = $user_data['lastname'] . ' ' . $user_data['firstname'] . ' <' . $user_data['email'] . '>';

    // Mail subject
    $emailSubject  = $langYourReg . ' ' . $siteName;
  
   	$serverAddress = $rootWeb.'index.php';

    if ( $courseRegSucceed )
   	{
        // Mail body
	    $emailBody = "$langDear %s %s ,\n"
                    . "$langOneResp " . $_course['officialCode'] . " $langRegYou $siteName $langSettings %s\n"
                    . "$langPassword: %s \n"
                    . "$langAddress $siteName $langIs: $serverAddress\n"
                    . "$langProblem\n"
                    . "\n"
                    . "$langFormula,\n"
                    . "$langAdministrator $administrator_name \n"
                    . "$langManager $siteName\n";
    
         $emailBody = sprintf($emailBody,$user_data['firstname'],$user_data['lastname'], $user_data['email'],$user_data['password']);

         if ( ! empty($administrator_phone) ) $emailBody .= "T. $administrator_phone \n";
         $emailBody .= "$langEmail : $administrator_email \n";

	     $messageList[]= sprintf("$langTheU %s %s $langAddedToCourse.",$user_data['firstname'],$user_data['lastname']);
    }
    else
    {
        // why not ???
        $emailBody = "$langDear %s %s,\n"
                     . "$langYouAreReg $siteName $langSettings %s \n"
                     . "$langPassword: %s \n"
                     . "$langAddress $siteName $langIs: $serverAddress \n"
                     . "$langProblem\n"
                     . "\n"
                     . "$langFormula, \n"
                     . "$langAdministrator $administrator_name \n"
                     . "$langManager $siteName\n";
    
         $emailBody = sprintf($emailBody,$user_data['firstname'],$user_data['lastname'], $user_data['email'],$user_data['password']);

         if ( ! empty($administrator_phone) ) $emailBody .= "T. $administrator_phone \n";
         $emailBody .= "$langEmail: $administrator_email \n";
    
		 $messageList[] = sprintf("%s %s added to platform.",$user_data['firstname'],$user_data['lastname']);
	}

    // Send mail 
    if ( ! empty($user_data['email']) ) claro_mail_user($user_id, $emailBody, $emailSubject);
}

/*=====================================================================
 Display Section
 =====================================================================*/ 

// display header
include($includePath.'/claro_init_header.inc.php');

claro_disp_tool_title(array('mainTitle' =>$nameTools, 'supraTitle' => $langUsers),
				'help_user.php');

// message box

if ( count($messageList) > 0 ) 
{
    claro_disp_message_box( implode('<br />', $messageList) );
}

if ( $platformRegSucceed ) 
{
    echo '<p><a href="user.php"><< ' .  $langBackToUsersList . '</a></p>' . "\n";
}
else 
{

    echo $langOneByOne; 
    echo '<p>' . $langUserOneByOneExplanation . '</p>' . "\n";

    user_display_form_add_new_user($user_data);

}

// display footer
include($includePath.'/claro_init_footer.inc.php');

/**

    OLD CODE FROM 1.6 TO PREVENT CLASH

    IT IS DEPRECATED 
	
    // prevent conflict with existing user account

	if ( $dataChecked )
	{
		$sql = "SELECT user_id,
		                       (username='".$username_form."') AS loginExists,
		                       (nom='".$nom_form."' AND prenom='".$prenom_form."' AND email='".$email_form."') AS userExists
		                     FROM `".$tbl_user."`
		                     WHERE username='".$username_form."' OR (nom='".$nom_form."' AND prenom='".$prenom_form."' AND email='".$email_form."')
		                     ORDER BY userExists DESC, loginExists DESC";

        echo $sql;
		
		$result=claro_sql_query($sql);
                		
		if ( mysql_num_rows($result) )
		{
			while ( $user=mysql_fetch_array($result) )
			{
				// check if the user is already registered to the platform

				if ( $user['userExists'] )
				{
				    $userExists  = true;
				    $userId      = $user['user_id'];
				    $message     = $langUserNameTaken;
				    break;
				}
				else
				{
				    $userExists = false;
				}

				// check if the login name choosen is already taken by another user

				if ( $user['loginExists'] )
				{
				    $loginExists = true;
				    $userId      = 0;

				    $message     = $langUserNo." (".$username_form.") ".$langTaken;

				    break;
				}
				else
				{
				    $loginExists = false;
				}
			}				// end while $result
		}					// end if num rows
	}						// end if datachecked

*/

?>
