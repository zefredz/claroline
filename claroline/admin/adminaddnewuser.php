<?php # $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------



$langFile = "admin";
include("../inc/claro_init_global.inc.php");
include($includePath."/../lang/english/registration.inc.php");
include($includePath."/../lang/".$languageInterface."/registration.inc.php");

$nameTools 			= $langAddUser;

$interbredcrump[] = array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);
$noQUERY_STRING   = TRUE;

include($includePath."/lib/text.lib.php");
include($includePath."/lib/debug.lib.inc.php");
include($includePath."/lib/userManage.lib.php");


$is_allowedToAdmin 	= $is_platformAdmin;

//TABLES USED

$tbl_user 			= $mainDbName."`.`user";
$TABLEUSER          = $tbl_user;

$display_form		=TRUE;
$display_resultCSV	=FALSE;

//init banner

include($includePath."/claro_init_header.inc.php");

 /*==========================
   EXECUTE COMMAND SECTION
  ==========================*/

if($register=="yes")
{
    $regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,3})$";
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
        unset($password1, $password, $email);

        $dialogBox .= $langEmailWrong;
    }

    // CHECK IF THE LOGIN NAME IS ALREADY OWNED BY ANOTHER USER

    else
    {
        $result = mysql_query("SELECT user_id FROM `$TABLEUSER`
                               WHERE username=\"$uname\"");


        if (mysql_num_rows($result) > 0)
        {
            $regDataOk = false;
            $dialogBox .= $langUsernameTaken;
            unset($password1, $password, $uname);
        }
        else
        {
            $regDataOk = true;
        }
    }
}

if ( ! $regDataOk)
{
    $display_form = true;
}


/*> > > > > > > > > > > > REGISTRATION ACCEPTED < < < < < < < < < < < <*/

if ($regDataOk)
{
    /*-----------------------------------------------------
      STORE THE NEW USER DATA INSIDE THE CLAROLINE DATABASE
      -----------------------------------------------------*/

    mysql_query("INSERT INTO `".$TABLEUSER."`
                 SET `nom`          = \"".$nom."\",
                     `prenom`       = \"".$prenom."\",
                     `username`     = \"".$uname."\",
                     `password`     = \"".($userPasswordCrypted?md5($password):$password)."\",
                     `email`        = \"".$email."\",
                     `statut`       = \"".$statut."\",
                     `officialCode`    = \"".$officialCode."\"
                     ");

    //$_uid = mysql_insert_id();

    $inserted_uid = mysql_insert_id();

    /*--------------------------------------
                 EMAIL NOTIFICATION
      --------------------------------------*/


    // Lets predefine some variables. Be sure to change the from address!

    $emailto       = "\"$prenom $nom\" <$email>";
    $emailfromaddr =  $administrator["email"];
    $emailfromname = "$siteName";
    $emailsubject  = "[".$siteName."] $langYourReg";

    // The body can be as long as you wish, and any combination of text and variables

    $emailbody    = "$langDear $prenom $nom,\n
    $langYouAreReg $siteName $langSettings $uname\n$langPass : $password\n$langAddress $siteName $langIs : $rootWeb\n$langProblem\n$langFormula,\n" .
    $administrator["name"] . "\n $langManager $siteName\nT. " . $administrator["phone"] . "\n$langEmail : " . $administrator["email"] . "\n";

    // Here we are forming one large header line
    // Every header must be followed by a \n except the last
    $emailheaders = "From: " . $administrator["name"] . " <".$administrator["email"].">\n";
    $emailheaders .= "Reply-To: " . $administrator["email"] . "";

    // Because I predefined all of my variables, this mail() function looks nice and clean hmm?
    @mail( $emailto, $emailsubject, $emailbody, $emailheaders);

    $display_form = false;
    $display_success = true;

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
<form method="post" action="<?= $PHP_SELF ?>?register=yes">
	<table cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td align="right"><?php echo $langLastName; ?> :
		</td>
		<td>
		<input type="text" size="15" name="nom" value="<?php echo htmlentities(stripslashes($nom)); ?>">
		</td>
	</tr>
	<tr>
		<td align="right"><?php echo $langFirstName; ?> :
		</td>
		<td>
		<input type="text" size="15" name="prenom" value="<?php echo htmlentities(stripslashes($prenom)); ?>">
		</td>
	</tr>
	<tr>
		<td align="right"><?= $langUsername ?> :
		</td>
		<td><input type="text" size="15" name="uname" value="<?php echo htmlentities(stripslashes($uname)); ?>">
		</td>
	</tr>
    <tr>
        <td align="right"><?php echo $langPassword ?> :
        </td>
        <td>
        <input type="password" size="15" name="password" value="">
        </td>
    </tr>
    <tr>
        <td align="right"><?php echo $langConfirm ?> :
        </td>
        <td>
        <input type="password" size="15" name="password1" value="">
        </td>
    </tr>
	<tr>
		<td align="right"><?php echo $langEmail; ?> :
		</td>
		<td>
		<input type="text" size="30" name="email" value="<?php echo $email; ?>">
		</td>
	</tr>
	<tr>
		<td align="right"><?php echo $langAction; ?> :
		</td>
		<td>
        <select name="statut_form">
         <option value="5"><?php echo $langFollowCourse; ?></option>
         <option value="1"><?php echo $langCreateCourse; ?></option>
        </select>
		</td>
	</tr>
	<tr>
		<td>&nbsp;
		</td>
		<td><input type="submit" name="submit" value="<?php echo  $langCreateUser ?>">
		</td>
	</tr>
	</table>
</form>
<?php
} //end display form

if ($display_success)
{
   echo $langUserCreated."<br><br>";
   echo "<a class=\"claroButton\" href=\"../auth/courses.php?cmd=rqReg&uidToEdit=".$inserted_uid."&category=\"> ".$langRegister." </a>";
   echo "<a class=\"claroButton\" href=\"adminprofile.php?uidToEdit=".$inserted_uid."&category=\"> ".$langGoToUserSettings." </a>";
   echo "<a class=\"claroButton\" href=\"adminaddnewuser.php\"> ".$langCreateAnotherUser." </a>";
   echo "<a class=\"claroButton\" href=\"adminadduserlist.php\"> ".$langAddaListOfUsers." </a>";
   echo "<a class=\"claroButton\" href=\"index.php\"> ".$langBackToAdmin." </a>";
}
else
{
   echo "<a class=\"claroButton\" href=\"adminadduserlist.php\"> ".$langAddaListOfUsers." </a>";
}

include($includePath."/claro_init_footer.inc.php");
?>