<?php
// $Id$
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

define ("NB_LINE_OF_EVENTS", 15);
define ("CHECK_PASS_EASY_TO_FIND", true);
define ("USER_SELECT_FORM", 1);
define ("USER_DATA_FORM", 2);

$langFile='admin';
$cidReset = true;
include('../inc/claro_init_global.inc.php');
include($includePath.'/lib/text.lib.php');

$nameTools=$langModifOneProfile;

$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);
//$interbredcrump[]= array ("url"=>$rootAdminWeb."/adminusers.php"=> $langManage);

$htmlHeadXtra[] = "<style type=\"text/css\">
<!--
	body,h1,h2,h3,h4,h5,h6,p,blockquote,td,ol,ul {font-family: Arial, Helvetica, sans-serif; }
-->
</STYLE>";

$htmlHeadXtra[] =
            "<script>
            function confirmation (name)
            {
                if (confirm(\"".$langAreYouSureToDelete."\"+ name + \"? \"))
                    {return true;}
                else
                    {return false;}
            }
            </script>";


$tbl_log 	= $mainDbName."`.`loginout";
$tbl_user 	= $mainDbName."`.`user";
$tbl_admin  = $mainDbName."`.`admin";
$tbl_course = $mainDbName."`.`cours";
$tbl_course_user = $mainDbName."`.`cours_user";

include($includePath.'/claro_init_header.inc.php');

//--------------------------------------------------------------------------------------------------------------------
// FIRST PART : USER's PERSONNAL INFORMATION
//--------------------------------------------------------------------------------------------------------------------

// deal with sesison variables (must unset variables if come back from enroll script)

session_unregister("userEdit");


// see which user we are working with ...

$user_id = $_REQUEST['uidToEdit'];
//echo $user_id."<br>";

//------------------------------------
// Execute COMMAND section
//------------------------------------

if (isset($applyChange))  //for formular modification
{
    $regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,3})$";

    #########" Look for name already taken ##############################
    $username_form		= trim ($username_form);
    $nom_form			= trim ($nom_form);
    $prenom_form		= trim ($prenom_form);
    $email_form			= trim ($email_form);

    $username_check = mysql_query(
    "SELECT	username
    FROM `".$tbl_user."`
    WHERE username='$username_form'") or die("Erreur SELECT FROM user");

    while ($myusername = mysql_fetch_array($username_check))
    {
    	$user_exist=$myusername[0];
    }

    ######## No empty field ########################

    if ( empty($nom_form)
    	OR empty($prenom_form)
    	OR empty($username_form)
    	OR (empty($email_form) && !$userMailCanBeEmpty)
    		)
    {
    	$classMsg = "warning";
    	$dialogBox = 	$langFields;
    }

    ################# verify that user is free #################

    elseif(($username_form==$user_exist) AND ($username_form!=$uname))
    {
    	$classMsg = "warning";
    	$dialogBox = $langUserTaken;
    }
    ################### Check email synthax #####################

    elseif( !empty($email_form) && !eregi( $regexp, $email_form )) // (empty($email_form) && !$userMailCanBeEmpty) is tested before
    {
    	$classMsg = "warning";
    	$dialogBox = $langEmailWrong;
    }
    ################### Check password entered are the same (if reset password is asked)#####################

    elseif( !empty($password) && ($password!=$confirm)) // attempt to change the user password but confirm is not the same
    {
        $classMsg = "warning";
        $dialogBox = $langPasswordWrong;
    }
    else  //apply changes as no mistake in the form was found
    {

    	mysql_query(
        "UPDATE  `".$tbl_user."`
         SET
         nom='$nom_form',
         prenom='$prenom_form',
    	 username='$username_form', email='$email_form',
         officialCode='$official_code_form',
         phoneNumber='$userphone_form'
         WHERE user_id='$user_id'");

    	if ($user_id==$_uid)
    	{
    		$uidReset	= true;
    		include($includePath.'/claro_init_local.inc.php');
    	}
    	$classMsg = "success";
        $dialogBox = $langAppliedChange;

        // set user admin parameter

        if (($admin_form=="no"))  //do not set as admin
        {
           mysql_query(
           "DELETE FROM `".$tbl_admin."`
           WHERE idUser='$user_id'");
        }

        elseif ($admin_form=="yes")  // if admin, we must check if the user was already admin
        {
           $sql = "SELECT * FROM `".$tbl_admin."`
                   WHERE idUser='$user_id'
                   ";
           $resultAdmin =  claro_sql_query($sql);
           $numadmin = mysql_numrows($resultAdmin);

           if ($numadmin==0)
           {
              $sql = "INSERT INTO `".$tbl_admin."` (idUser) VALUES (".$user_id.")";
              claro_sql_query($sql);
           }
        }

       //set user ""can create course"" or not

       if ($create_course_form=="yes")
       {
         $sql = "UPDATE  `".$tbl_user."`
                 SET
                 statut='1'
                 WHERE user_id='$user_id'";
         claro_sql_query($sql);
       }
       elseif ($create_course_form=="no")
       {
         $sql = "UPDATE  `".$tbl_user."`
                 SET
                 statut='5'
                 WHERE user_id='$user_id'";
         claro_sql_query($sql);
       }

       // change user password if it has been asked

       if (!empty($password) && !empty($confirm) && ($confirm==$password))
       {
            if ($userPasswordCrypted) $password = md5(trim($password));
            $sql = "UPDATE  `".$tbl_user."`
                 SET
                 password='$password'
                 WHERE user_id='$user_id'";
            claro_sql_query($sql);
            
       }

    }


	$display = USER_DATA_FORM;
}	// IF applyChange

if(isset($user_id))

{
    //find global user info

    $sqlGetInfoUser ="
	SELECT *
		FROM  `".$tbl_user."`
		WHERE user_id='$user_id'";
	$result=mysql_query($sqlGetInfoUser) or die("Erreur SELECT FROM user");
    //echo $sqlGetInfoUser;

	$myrow = mysql_fetch_array($result);

    $user_id =            $myrow[user_id];
	$nom_form = 		  $myrow[nom];
	$prenom_form = 		  $myrow[prenom];
    $official_code_form = $myrow[officialCode];
	$username_form = 	  $myrow[username];
	$email_form	=		  $myrow[email];
    $userphone_form =     $myrow[phoneNumber];
	$display = USER_DATA_FORM;

    // find user type, must see if the user is in the admin table.

    $sql = "SELECT * FROM `".$tbl_admin."`
            WHERE idUser='$user_id'
            ";

    $resultAdmin = claro_sql_query($sql);
    $num = mysql_numrows($resultAdmin);
    //echo $sql." : ".$num;
    if ($num != 0)
    {
        $isAdmin = true;
    }
    else
    {
        $isAdmin = false;
    }

    if ($myrow[statut]==1)
    {
     $canCreateCourse = true;
    }
    else
    {
     $canCreateCourse = false;
    }

}

session_unregister("uname");
session_unregister("pass");
session_unregister("nom");
session_unregister("prenom");

$uname	=	$username_form;
$nom	=	$nom_form;
$prenom	=	$prenom_form;

session_register("uname");
session_register("nom");
session_register("prenom");

if (isset($cmd))
{
    if ($cmd=="delete")
    {
        $dialogBox = "delete called";
    }

}



//------------------------------------
// DISPLAY
//------------------------------------


// Display tool title

claro_disp_tool_title($nameTools);

//Display Forms or dialog box(if needed)

if($dialogBox)
  {
    claro_disp_message_box($dialogBox);
  }

//Display "form and info" about the user

if ($display == USER_DATA_FORM)
{

    // display subtitle
    echo "<h4>".$langPersonnalInfo."</h4>";
?>

<form method="POST" action="<?php echo $PHP_SELF ?>">
<input type="hidden" name="applyChange" value="yes">
<table width="100%" >

     <tr>
        <td valign="top">
            <?php echo $langUserid ?> :
        </td>
        <td colspan="2">
            <?php echo $user_id ?>
        </td>
    </tr>

	<tr>
		<td valign="top">
			<?php echo $langName ?> :
		</td>
		<td colspan="2">
			<input type="text" size="40" name="nom_form" value="<?php echo $nom_form ?>">
		</td>
	</tr>

    <tr>
        <td valign="top">
            <?php echo $langFirstName ?> :
        </td>
        <td colspan="2">
            <input type="text" size="40" name="prenom_form" value="<?php echo $prenom_form ?>">
        </td>
    </tr>

    <tr>
        <td valign="top">
            <?php echo $langOfficialCode ?> :
        </td>
        <td colspan="2">
            <input type="text" size="40" name="official_code_form" value="<?php echo $official_code_form ?>">
        </td>
    </tr>

	<tr>
	  <td valign="top">
	    <?=$langUsername?> :
	  </td>
	  <td colspan="2">
	    <input type="text" size="40" name="username_form" value="<?=$username_form?>">
	  </td>
	</tr>

    <tr>
      <td colspan="2">
        <small>(<?=$langChangePwdexp?>)</small>
      </td>
    </tr>

    <tr>
      <td valign="top">
        <?=$langPassword?> :
      </td>
      <td colspan="2">
        <input type="password" name="password" id="password" size="40" value="<?php echo $password ?>">
      </td>
    </tr>

    <tr>
      <td valign="top">
        <?=$langConfirm?> :
      </td>
      <td colspan="2">
        <input type="password" name="confirm" id="confirm" size="40" value="<?php echo $confirm ?>">
      </td>
    </tr>

	<tr>
	  <td valign="top">
	    <?=$langEmail?> :
	  </td>
	  <td colspan="2">
	    <input type="text" size="40" name="email_form" value="<?=$email_form?>">
	    <br>
      </td>
   </tr>

   <tr>
       <td><?=$langPhone?> : </td>
       <td>
         <input type="text" size="40" name="userphone_form" value="<?=$userphone_form?>">
       </td>
    </tr>

        <tr>
       <td><?=$langUserCanCreateCourse?> : </td>
       <td>
         <input type="radio" name="create_course_form" value="yes" <? if ($canCreateCourse) { echo "checked"; }?> ><?=$langYes?></input>
         <input type="radio" name="create_course_form" value="no"  <? if (!$canCreateCourse){ echo "checked"; }?> ><?=$langNo?></input>
       </td>
    </tr>

    <tr>
       <td><?=$langUserIsPlaformAdmin ?> : </td>
       <td>
         <input type="radio" name="admin_form" value="yes" <? if ($isAdmin) { echo "checked"; }?> ><?=$langYes?></input>
         <input type="radio" name="admin_form" value="no" <? if (!$isAdmin) { echo "checked"; }?> ><?=$langNo?></input>
       </td>
    </tr>

	<tr>
	  <td>
	  </td>
	  <td colspan="2">
	    <input type="hidden" name="uidToEdit" value="<?=$user_id?>">
        <input type="hidden" name="cfrom" value="<?=$cfrom?>">
	    <input type="submit" name="applyChange" value="<?=$langSaveChanges?>">
	    <br>
      </td>
   </tr>
  </table>
</form>
<?php
}
else
{
	echo "Script error";
}

// display TOOL links :

echo "<a class=\"claroButton\"
       onClick=\"return confirmation('",addslashes($username_form),"');\"
       href=\"adminuserdeleted.php?uidToEdit=".$user_id."&cmd=delete\" > ".$langDeleteUser." </a>\n";
echo "<a class=\"claroButton\" href=\"../auth/courses.php?cmd=rqReg&uidToEdit=".$user_id."&category=\" >  ".$langRegisterUser." </a>\n";
echo "<a class=\"claroButton\" href=\"adminusercourses.php?uidToEdit=".$user_id."\"> ".$langUserCourseList." </a>\n";

if (isset($cfrom) && $cfrom=="ulist")  //if we come form user list, we must display go back to list
{
    echo "<a class=\"claroButton\" href=\"adminusers.php\"> ".$langBackToUserList." </a>\n";
}
// display footer

include($includePath."/claro_init_footer.inc.php");
?>