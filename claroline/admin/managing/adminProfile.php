<?php // $Id$
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



$langFile='registration';
$cidReset = true;
include('../../inc/claro_init_global.inc.php');
include($includePath.'/lib/text.lib.php');

$nameTools=$langModifProfile;

$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);
$interbredcrump[]= array ("url"=>$rootAdminWeb."managing/", "name"=> $langManage);

$htmlHeadXtra[] = "<style type=\"text/css\">
<!--
	body,h1,h2,h3,h4,h5,h6,p,blockquote,td,ol,ul {font-family: Arial, Helvetica, sans-serif; }
-->
</STYLE>";


$tbl_log 	= $mainDbName."`.`loginout";
$tbl_user 	= $mainDbName."`.`user";

include($includePath.'/claro_init_header.inc.php');

claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools
	)
	);
/******************************* IF applyChange **********************************************/
if (isset($applyChange))
{
	$regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,3})$";

#########" RECHERCHE USERNAME DEJA PRIS ##############################
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

######## VERIFIER QU'IL N'Y A PAS DE CHAMP VIDE ########################

	if ( empty($nom_form)
		OR empty($prenom_form)
		OR empty($username_form)
		OR (empty($email_form) && !$userMailCanBeEmpty)
			)
	{
		$classMsg = "warning";
		$msgstr = 	$langFields;
	}

################# VERIFIER QUE LE USERNAME EST LIBRE #################

	elseif(($username_form==$user_exist) AND ($username_form!=$uname))
	{
		$classMsg = "warning";
		$msgstr = 	$langUserTaken;
	}
## VERIFIER LA SYNTAXE ET LA VALIDITE DE L'EMAIL / ENVOYER UN EMAIL ##

	elseif( !empty($email_form) && !eregi( $regexp, $email_form )) // (empty($email_form) && !$userMailCanBeEmpty) is tested before
	{
		$classMsg = "warning";
		$msgstr = 	$langEmailWrong;
	}
/*      // Check's to see if the server has a functioning mail server.
        elseif (!checkdnsrr( $emailtohostname, "MX" )) {
            echo"<tr>
                 <td colspan=2>
                 L'adresse email <font color=navy>$email</font > n'est pas valide.
                 Le serveur de messagerie ne répond pas.
                 Utilisez le bouton de retour en arrière de votre navigateur et
                 recommencez.
                 </td>
                 </tr>";
        }	*/
	else
	{

		mysql_query(
"UPDATE  `".$tbl_user."`
	SET nom='$nom_form', prenom='$prenom_form',
		username='$username_form', email='$email_form'
	WHERE user_id='$uidToEdit'");

		if ($uidToEdit==$_uid)
		{
			$uidReset	= true;
			include($includePath.'/claro_init_local.inc.php');
		}
		$classMsg = "success";
		$msgstr = 	$langUserProfileReg;
	}
	$display = USER_DATA_FORM;
}	// IF applyChange
elseif(isset($HTTP_GET_VARS["uidToEdit"]))
{
	$sqlGetInfoUser ="
	SELECT nom, prenom, username, email
		FROM  `".$tbl_user."`
		WHERE user_id='$uidToEdit'";
	$result=mysql_query($sqlGetInfoUser) or die("Erreur SELECT FROM user");
	//echo $sqlGetInfoUser;
	$myrow = mysql_fetch_array($result);

	$nom_form = 		$myrow[nom];
	$prenom_form = 		$myrow[prenom];
	$username_form = 	$myrow[username];
	$email_form	=		$myrow[email];
	$display = USER_DATA_FORM;
}
else
{
	$sqlGetListUser = "SELECT user_id, nom, prenom, username, email FROM  `".$tbl_user."` ORDER BY UPPER(`nom`), UPPER(`prenom`)";
	$resListOfUsers=mysql_query($sqlGetListUser) or die("Erreur SELECT FROM user");

	$display = USER_SELECT_FORM;
}
 /**************************************************************************************/


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


if (isset($msgstr))
{
	echo "<DIV class=\"",$classMsg,"\">",$msgstr,"</DIV><br>";
}
if ($display == USER_SELECT_FORM)
{
?>
<form action="<?php echo $PHP_SELF ?>" method="GET">
<select name="uidToEdit" tabindex=2>
<?php
	while ($user = mysql_fetch_array($resListOfUsers))
	{
		echo "
	<OPTION  value=\"",$user["user_id"],"\" >
		",$user["nom"]," ",$user["prenom"],"
		(",$user["username"],")
		",$user["email"],"
	</OPTION>";
	}
?>
</select>
<input type="submit" value="edit this user">
</form>
<?php
}
elseif ($display == USER_DATA_FORM)
{
?>
<form method="post" action="<?php echo $PHP_SELF ?>">
<input type="hidden" name="applyChange" value="yes">
<table width="100%">
	<tr>
		<td valign="top">
			<?php echo $langName ?>
		</td>
		<td colspan="2">
			<input type="text" size="40" name="nom_form" value="<?php echo $nom_form ?>">
		</td>
	</tr>
	<tr>
		<td valign="top">
			<?php echo $langSurname ?>
		</td>
		<td colspan="2">
			<input type="text" size="40" name="prenom_form" value="<?php echo $prenom_form ?>">
		</td>
<?php echo 
			"</tr>\n",
			"<tr>\n",
			"<td valign=\"top\">\n",
			"$langUsername\n",
			"</td>\n",
			"<td colspan=\"2\">\n",
			"<input type=\"text\" size=\"40\" name=\"username_form\" value=\"$username_form\">\n",
			"</td>\n",
			"</tr>\n",
			"<tr>\n",
			"<td valign=\"top\">\n",
			"$langEmail\n",
			"</td>\n",
			"<td colspan=\"2\">\n",
			"<input type=\"text\" size=\"40\" name=\"email_form\" value=\"$email_form\">\n",
			"<br>\n";

	echo	"</td>",
			"<tr>\n",
			"<td>\n",
			"</td>\n",
			"<td colspan=\"2\">\n",
			"<input type=\"hidden\" name=\"uidToEdit\" value=\"",$uidToEdit,"\">\n",
			"<input type=\"submit\" name=\"applyChange\" value=\"$langOk\">\n";
			?>
			<br>
		</td>
	</tr>
</table>
</form>
<?php
}
else
{
	echo "erreur de script";
}
?>
</table>
<HR>
<?php
	if ($uidToEdit)
	{
?>
<a href="adminCoursesOfAUser.php?uidToEdit=<?php echo $uidToEdit ?>"><?php echo $langCourses4User; ?></a>
<?
}
?>
<a href="userManagement.php"><?php echo $langCoursesByUser; ?></a>

<?php
include($includePath."/claro_init_footer.inc.php");
?>