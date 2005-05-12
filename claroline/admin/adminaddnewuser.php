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
$cidReset = TRUE;$gidReset = TRUE;$tidReset = TRUE;
require '../inc/claro_init_global.inc.php';
//SECURITY CHECK
$is_allowedToAdmin     = $is_platformAdmin;
if (!$is_allowedToAdmin) claro_disp_auth_form();


include($includePath."/lib/debug.lib.inc.php");
include($includePath."/lib/userManage.lib.php");
include($includePath."/lib/admin.lib.inc.php");
include($includePath."/conf/user_profile.conf.php");
include($includePath.'/lib/claro_mail.lib.inc.php');

$nameTools             = $langAddUser;

$interbredcrump[] = array ("url"=>$rootAdminWeb, "name"=> $langAdministration);
$noQUERY_STRING   = TRUE;

//TABLES USED
/*
 * DB tables definition
 */

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user = $tbl_mdb_names['user'];

$display_form        = TRUE;
$display_resultCSV    = FALSE;

$dialogBox = "";

//init banner

include($includePath."/claro_init_header.inc.php");

//retrieve needed parameters from URL to prefill creation form (in case of relaod with error to correct by user)
 
if (isset($_REQUEST['lastname']))      $lastname        = $_REQUEST['lastname'];      else $lastname = "";
if (isset($_REQUEST['firstname']))     $firstname       = $_REQUEST['firstname'];     else $firstname = "";
if (isset($_REQUEST['official_code'])) $official_code   = $_REQUEST['official_code']; else $official_code = "";
if (isset($_REQUEST['username']))      $username        = $_REQUEST['username'];      else $username = "";
if (isset($_REQUEST['password']))      $password        = $_REQUEST['password'];      else $password = "";
if (isset($_REQUEST['password1']))     $password1       = $_REQUEST['password1'];     else $password1 = "";
if (isset($_REQUEST['email']))         $email           = $_REQUEST['email'];         else $email = "";
if (isset($_REQUEST['phone']))         $phone           = $_REQUEST['phone'];         else $phone = "";

 /*==========================
   EXECUTE COMMAND SECTION
  ==========================*/

if(isset($_REQUEST['register']) && $_REQUEST['register']=="yes")
{
    $regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";
    $username   = trim ($HTTP_POST_VARS["username"  ]);
    $email      = trim ($HTTP_POST_VARS["email"     ]);
    $lastname   = trim ($HTTP_POST_VARS["lastname"  ]);
    $firstname  = trim ($HTTP_POST_VARS["firstname" ]);
    $password   = trim ($HTTP_POST_VARS["password"  ]);
    $password1  = trim ($HTTP_POST_VARS["password1" ]);
    $statut     = ($HTTP_POST_VARS["statut"    ]==COURSEMANAGER)?COURSEMANAGER:STUDENT;

    /*==========================
       DATA SUBIMITED CHECKIN
      ==========================*/

    // CHECK IF THERE IS NO EMPTY FIELD

    if (
           empty($lastname)
        OR empty($firstname)
        OR empty($password1)
        OR empty($password)
        OR empty($username)
        OR (empty($email) && !$userMailCanBeEmpty)
            )
    {
        $regDataOk = false;

        unset($password1, $password);

        $dialogBox .= $langEmptyFields;
    }

    // CHECK IF THE TWO PASSWORD TOKEN ARE IDENTICAL

    elseif($password1 != $password)
    {
        $regDataOk = false;
        unset($password1, $password);

        $dialogBox .= $langPassTwice;
    }

    // CHECK EMAIL ADDRESS VALIDITY

    elseif( !empty($email) && ! eregi( $regexp, $email ))
    {
        $regDataOk = false;
        unset($password1, $password);

        $dialogBox .= $langEmailWrong;
    }

    // CHECK IF THE LOGIN NAME IS ALREADY OWNED BY ANOTHER USER

    else
    {
        $sql = "SELECT `user_id` 
                FROM `".$tbl_user."`
                WHERE username=\"".$username."\"";
        $result = claro_sql_query($sql);


        if (mysql_num_rows($result) > 0)
        {
            $regDataOk = false;
            $dialogBox .= $langUserNameTaken;
            unset($password1, $password, $username);
        }
        else
        {
            $regDataOk = TRUE;
        }
    }
}

if (isset($regDataOk) && (!$regDataOk))
{
    $display_form = TRUE;
}


/*> > > > > > > > > > > > REGISTRATION ACCEPTED < < < < < < < < < < < <*/

if (isset($regDataOk) && ($regDataOk))
{
    /*-----------------------------------------------------
      STORE THE NEW USER DATA INSIDE THE CLAROLINE DATABASE
      -----------------------------------------------------*/

    $sql = "INSERT INTO `".$tbl_user."`
                 SET `nom`          = \"".$lastname."\",
                     `prenom`       = \"".$firstname."\",
                     `username`     = \"".$username."\",
                     `password`     = \"".($userPasswordCrypted?md5($password):$password)."\",
                     `email`        = \"".$email."\",
                     `phoneNumber`  = \"".$phone."\",
                     `statut`       = \"".$statut."\",
                     `officialCode`    = \"".$official_code."\"";
	// exec query and get last inserted id
    $inserted_uid = claro_sql_query_insert_id($sql);

    /*--------------------------------------
                 EMAIL NOTIFICATION
      --------------------------------------*/
	// do not event try to send the mail if there is no specified email address
	// mail address has already be checked via regex if set
	if( !empty($email) )
	{
    	$emailSubject  = '['.$siteName.'] '.$langYourReg;

    	$emailBody    = $langDear.' '.$firstname.' '.$lastname.",\n"
						.$langYouAreReg.' '.$siteName.' '.$langSettings.' '.$username."\n"
                        .$langPassword.' : '.$password."\n"
                        .$langAddress.' '.$siteName.' '.$langIs.' : '.$rootWeb."\n"
                        .$langProblem."\n"
                        .$langFormula.",\n"
                        .$administrator_name."\n"
                        .$langManager.' '.$siteName."\n"
						.'T. '. $administrator_phone."\n"
        				.$langEmail.' : '.$administrator_email."\n";

		claro_mail_user($inserted_uid, $emailBody, $emailSubject);
	}
		
    $display_form = false;
    $display_success = TRUE;

}

/*==========================
   OUTPUT : ADD ONE USER FORM
  ==========================*/

  // tool title

claro_disp_tool_title(
    array(
    'mainTitle'=>$nameTools
    )
);

if (isset($controlMsg)) claro_disp_msg_arr($controlMsg);

  // Display Forms or dialog box(if needed)

if( !empty($dialogBox) )
{
	claro_disp_message_box($dialogBox);
}


if($display_form)
{
    echo $langAddUserOneByOne;

?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>?register=yes">
    <table cellpadding="3" cellspacing="0" border="0">
    <tr>
        <td align="right">
            <label for="lastname"><?php echo $langLastName; ?></label> :
        </td>
        <td>
            <input type="text" size="40" name="lastname" id="lastname" value="<?php echo htmlentities(stripslashes($lastname)); ?>">
        </td>
    </tr>
    <tr>
        <td align="right">
            <label for="firstname"><?php echo $langFirstName; ?></label> :
        </td>
        <td>
            <input type="text" size="40" name="firstname" id="firstname" value="<?php echo htmlentities(stripslashes($firstname)); ?>">
        </td>
    </tr>

    <tr>
        <td align="right">
            <label for="official_code"><?php echo $langOfficialCode; ?></label> :
        </td>
        <td>
            <input type="text" size="40" name="official_code" id="official_code" value="<?php echo htmlentities(stripslashes($official_code)); ?>">
        </td>
    </tr>

    <tr>
      <td><br></td>
    </tr>
    <tr>
      <td></td>
    </tr>
    <tr>
        <td align="right">
            <label for="username"><?php echo $langUserName ?></label> :
        </td>
        <td>
            <input type="text" size="40" name="username" id="username" value="<?php echo htmlentities(stripslashes($username)); ?>">
        </td>
    </tr>
    <tr>
        <td align="right">
            <label for="password"><?php echo $langPassword ?></label> :
        </td>
        <td>
            <input type="password" size="40" name="password"  id="password" value="">
        </td>
    </tr>
    <tr>
        <td align="right">
            <label for="password1"><?php echo $langConfirm ?></label> :
        </td>
        <td>
            <input type="password" size="40" name="password1" id="password1" value="">
        </td>
    </tr>
    <tr>
      <td><br></td>
    </tr>
    <tr>
      <td></td>
    </tr>
    <tr>
        <td align="right">
            <label for="email"><?php echo $langEmail; ?></label> :
        </td>
        <td>
            <input type="text" size="40" name="email" id="email" value="<?php echo $email; ?>">
        </td>
    </tr>
    <tr>
        <td align="right">
            <label for="phone"><?php echo $langPhone; ?></label> :
        </td>
        <td>
            <input type="text" size="40" name="phone" id="phone" value="<?php echo $phone; ?>">
        </td>
    </tr>
    <tr>
        <td align="right">
             <label for="statut"><?php echo $langAction; ?></label>
              :
        </td>
        <td>
            <select name="statut" id="statut">
                <option value="5"><?php echo $langRegStudent; ?></option>
                <option value="1"><?php echo $langCreateCourse; ?></option>
            </select>
        </td>
    </tr>
    <tr>
        <td>
            &nbsp;
        </td>
        <td>
            <input type="submit" name="submit" value="<?php echo  $langCreateUser ?>">
        </td>
    </tr>
    </table>
</form>
<?php
} //end display form

if (isset($display_success))
{
   echo $langUserCreated."<br /><br />
   <ul>";
   echo "<li><a class=\"claroCmd\" href=\"../auth/courses.php?cmd=rqReg&uidToEdit=".$inserted_uid."&category=&fromAdmin=settings\"> ".$langRegisterTheNewUser." </a></li>";
   echo "<li><a class=\"claroCmd\" href=\"adminprofile.php?uidToEdit=".$inserted_uid."&category=\"> ".$langGoToUserSettings." </a></li>";
   echo "<li><a class=\"claroCmd\" href=\"adminaddnewuser.php\"> ".$langCreateAnotherUser." </a></li>";
   echo "<li><a class=\"claroCmd\" href=\"index.php\"> ".$langBackToAdmin." </a></li>
   </ul>
   ";
}

include($includePath."/claro_init_footer.inc.php");
?>
