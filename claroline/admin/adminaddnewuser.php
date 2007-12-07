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

//init banner

include($includePath."/claro_init_header.inc.php");

//clean session with used variables for name,... used in other scripts

$_SESSION['nom']    = "";
$_SESSION['prenom'] = "";
$_SESSION['uname']  = "";

 /*==========================
   EXECUTE COMMAND SECTION
  ==========================*/

if($register=="yes")
{
    $regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";
    $uname      = trim ($HTTP_POST_VARS["uname"     ]);
    $email      = trim ($HTTP_POST_VARS["email"     ]);
    $nom        = trim ($HTTP_POST_VARS["nom"       ]);
    $prenom     = trim ($HTTP_POST_VARS["prenom"    ]);
    $password   = trim ($HTTP_POST_VARS["password"  ]);
    $password1  = trim ($HTTP_POST_VARS["password1" ]);
    $statut     = ($HTTP_POST_VARS["statut"    ]==COURSEMANAGER)?COURSEMANAGER:STUDENT;

    /*==========================
       DATA SUBIMITED CHECKIN
      ==========================*/

    // CHECK IF THERE IS NO EMPTY FIELD

    if (
           empty($nom)
        OR empty($prenom)
        OR empty($password1)
        OR empty($password)
        OR empty($uname)
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
                WHERE username=\"".$uname."\"";
        $result = claro_sql_query($sql);


        if (mysql_num_rows($result) > 0)
        {
            $regDataOk = false;
            $dialogBox .= $langUserNameTaken;
            unset($password1, $password, $uname);
        }
        else
        {
            $regDataOk = TRUE;
        }
    }
}

if ( ! $regDataOk)
{
    $display_form = TRUE;
}


/*> > > > > > > > > > > > REGISTRATION ACCEPTED < < < < < < < < < < < <*/

if ($regDataOk)
{
    /*-----------------------------------------------------
      STORE THE NEW USER DATA INSIDE THE CLAROLINE DATABASE
      -----------------------------------------------------*/

    claro_sql_query("INSERT INTO `".$tbl_user."`
                 SET `nom`          = \"".$nom."\",
                     `prenom`       = \"".$prenom."\",
                     `username`     = \"".$uname."\",
                     `password`     = \"".($userPasswordCrypted?md5($password):$password)."\",
                     `email`        = \"".$email."\",
                     `phoneNumber`  = \"".$phone."\",
                     `statut`       = \"".$statut."\",
                     `officialCode`    = \"".$official_code."\"
                     ");

    //$_uid = mysql_insert_id();

    $inserted_uid = mysql_insert_id();

    /*--------------------------------------
                 EMAIL NOTIFICATION
      --------------------------------------*/


    // Lets predefine some variables. Be sure to change the from address!

    $emailto       = "\"$prenom $nom\" <$email>";
    $emailfromaddr = $administrator_email;
    $emailfromname = $siteName;
    $emailsubject  = '['.$siteName.'] '.$langYourReg;

    // The body can be as long as you wish, and any combination of text and variables

    $emailbody    = "$langDear $prenom $nom,\n
    $langYouAreReg $siteName $langSettings $uname\n$langPassword : $password\n$langAddress $siteName $langIs : $rootWeb\n$langProblem\n$langFormula,\n" .
    $administrator_name . "\n $langManager $siteName\nT. " . $administrator_phone . "\n$langEmail : " . $administrator_email . "\n";

    // Here we are forming one large header line
    // Every header must be followed by a \n except the last
    $emailheaders = "From: " . $administrator_name . " <".$administrator_email.">\n";
    $emailheaders .= "Reply-To: " . $administrator_email . "";

    // Because I predefined all of my variables, this mail() function looks nice and clean hmm?
    @mail( $emailto, $emailsubject, $emailbody, $emailheaders);

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

claro_disp_msg_arr($controlMsg);

  // Display Forms or dialog box(if needed)

if($dialogBox)
  {
    claro_disp_message_box($dialogBox);
  }


if($display_form)
{
    echo $langAddUserOneByOne; ?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>?register=yes">
    <table cellpadding="3" cellspacing="0" border="0">
    <tr>
        <td align="right">
            <label for="nom"><?php echo $langLastName; ?></label> :
        </td>
        <td>
            <input type="text" size="40" name="nom" id="nom" value="<?php echo htmlentities(stripslashes($nom)); ?>">
        </td>
    </tr>
    <tr>
        <td align="right">
            <label for="prenom"><?php echo $langFirstName; ?></label> :
        </td>
        <td>
            <input type="text" size="40" name="prenom" id="prenom" value="<?php echo htmlentities(stripslashes($prenom)); ?>">
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
            <label for="uname"><?php echo $langUserName ?></label> :
        </td>
        <td>
            <input type="text" size="40" name="uname" id="uname" value="<?php echo htmlentities(stripslashes($uname)); ?>">
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

if ($display_success)
{
   echo $langUserCreated."<br><br>
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
