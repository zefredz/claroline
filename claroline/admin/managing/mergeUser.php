<?php # $Id$

$lang_uid_to_add		= "Uid à ajouter";
$lang_name_to_add		= "Nom";
$lang_official_to_add	= "NOMA";
$lang_firstname_to_add	= "Prénom";
$lang_username_to_add	= "Nom d'utilisateur";
$lang_email_to_add		= "email";

$lang_merge_proceed		= "Effectuer la fusion";


$lang_no_back			= "Irréversible !!!";
$langAdministrationTools = "Outils d'administration";
$langManage ="Gestion du Campus";

#### SETTINGS ####
$_tid				= "MERGE_USER";

// these following information can be later found  in the central tool table during global init.
$_tool["pathId"] 		= "merge_user";
$_tool["nameVarName"] 	= "langMergeUsers";
$_tool["langFile"] 		= "admin.merge.users";


$langFile = $_tool["langFile"];
require '../../inc/claro_init_global.inc.php';
$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);
$interbredcrump[]= array ("url"=>$rootAdminWeb."managing/", "name"=> $langManage);
$nameTools = $$_tool["nameVarName"];

$tbl_user			= $mainDbName."`.`user";
$tbl_user_bak		= $mainDbName."`.`user_garbage";
$tbl_course_user  	= $mainDbName."`.`cours_user";
$tbl_course		= $mainDbName."`.`cours";
$tbl_admin		= $mainDbName."`.`admin";
$tbl_track_default	= $statsDbName."`.`track_e_default";// default_user_id
$tbl_track_access	= $statsDbName."`.`track_e_access";	// access_user_id
$tbl_track_login	= $statsDbName."`.`track_e_login";	// login_user_id
$tbl_track_link		= $statsDbName."`.`track_e_links";	//links_user_id
$tbl_track_upload	= $statsDbName."`.`track_e_uploads";// upload_user_id



//include($includePath."/conf/".$_tool["pathId"].".conf.php");
@include($includePath."/lib/debug.lib.inc.php");
include ($includePath."/lib/main.db.lib.inc.php");


$sql = "CREATE TABLE IF NOT EXISTS `".$tbl_user_bak."` (
  idGarbage int(10) unsigned NOT NULL auto_increment,
  user_id mediumint(8) unsigned NOT NULL default '0',
  nom varchar(60) default NULL,
  prenom varchar(60) default NULL,
  username varchar(20) default 'empty',
  password varchar(50) default 'empty',
  authSource varchar(50) default 'claroline',
  email varchar(100) default NULL,
  statut tinyint(4) default NULL,
  officialCode varchar(40) default NULL,
  phoneNumber varchar(30) default NULL,
  pictureUri varchar(250) default NULL,
  creatorId mediumint(8) unsigned default NULL,
  deletedBy mediumint(8) unsigned default NULL,
  deletionDate timestamp(14) NOT NULL,
  deletionCode tinyint(3) unsigned default NULL,
  deletionComment varchar(255) default NULL,
  PRIMARY KEY  (idGarbage)
) TYPE=MyISAM;";
@mysql_query($sql);


#### COMMANDS ####
if ($HTTP_GET_VARS["sniffTwice"]==1)
{
	$sql = "SELECT Distinct email, count(user_id) nbUserWithSameEmail
				FROM `".$tbl_user."`
			GROUP BY email
			HAVING nbUserWithSameEmail>1
			ORDER BY nbUserWithSameEmail DESC
			";
	$resUsersToSuspect = mysql_query_dbg($sql);

	while ($userToSuspect = mysql_fetch_array($resUsersToSuspect,MYSQL_ASSOC))
	{
		$usersToSuspect[] = $userToSuspect;
		$msg[] = "Email utilisé par plusieurs utilisateurs : ". $userToSuspect["email"]." : ". $userToSuspect["nbUserWithSameEmail"]." exemplaires";
	}

}

if ($HTTP_POST_VARS["run_merge"]=="do")
{
	$msg[]="tentative de fusion";
	if (is_numeric($HTTP_POST_VARS["uid_to_keep"]) && in_array($HTTP_POST_VARS["uid_to_keep"],$HTTP_POST_VARS["uid_to_merge"] ))
	{
		$HTTP_POST_VARS["uid_to_merge"] = array_diff($HTTP_POST_VARS["uid_to_merge"],array(""));
		$msg[]="On garde ".$HTTP_POST_VARS["uid_to_keep"];
		$msg[]="On liquide ".implode(array_diff($HTTP_POST_VARS["uid_to_merge"],array($HTTP_POST_VARS["uid_to_keep"])),", ")."";

		$users_to_remove = array_diff($HTTP_POST_VARS["uid_to_merge"],array($HTTP_POST_VARS["uid_to_keep"]));
		foreach($users_to_remove as $user_to_merge )
		{
			mergeUsersAccount($HTTP_POST_VARS["uid_to_keep"],$user_to_merge);
		}

	}
	else
	{
		$msg[]="Il faut désigner un survivant (cliquez sur retour)";
	}


}
else
{
	$display_what_merge = true;

	if ($HTTP_POST_VARS["uid_to_merge"])
	{
		$uid_to_merge		= array_diff($HTTP_POST_VARS["uid_to_merge"],array(""));

		$official_to_add	=$HTTP_POST_VARS["official_to_add"];
		$name_to_add		=$HTTP_POST_VARS["name_to_add"];
		$firstname_to_add	=$HTTP_POST_VARS["firstname_to_add"];
		$username_to_add	=$HTTP_POST_VARS["username_to_add"];
		$email_to_add		=$HTTP_POST_VARS["email_to_add"];

		$sql = "SELECT * from `".$tbl_user."` WHERE
			user_id in ('".implode("','",$uid_to_merge)."')
			".(!empty($official_to_add)	?"OR officialCode ='".	$official_to_add."'":"")."
			".(!empty($name_to_add)		?"OR nom LIKE '".		$name_to_add."'":"")."
			".(!empty($firstname_to_add)?"OR prenom LIKE '".	$firstname_to_add."'":"")."
			".(!empty($username_to_add)	?"OR username LIKE '".	$username_to_add."'":"")."
			".(!empty($email_to_add)	?"OR email LIKE '".		$email_to_add."'":"")."
			";
		$resUsersToMerge = mysql_query_dbg($sql);
		while ($userToMerge = mysql_fetch_array($resUsersToMerge,MYSQL_ASSOC))
		{
			$usersToMerge[] = $userToMerge;
		}
	}

}

#### WORK ####

#### OUTPUT ####
//// BANNER ////
include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=>$langUpgrade." - ".$PHP_AUTH_USER." - ".$siteName." - ".$clarolineVersion." - ".$dateNow
	)
	);
claro_disp_msg_arr($msg);


if ($display_what_merge)
{
?>
<form action="<?php $_SELF ?>" method="POST">
<?php
	if (is_array($usersToMerge))
	{
?>
<fieldset>
<TABLE class="claro_listInTable" cellspacing="2" cellpadding="2"  border="0" rules="" frame="box">
<THEAD>
	<TR>
		<Th >survivant</Th>
		<Th >confirm</Th>
		<Th >uid</Th>
		<Th >official</Th>
		<Th >prenom</Th>
		<Th >nom</Th>
		<Th >username</Th>
		<Th >password</Th>
		<Th >email</Th>
		<Th >statut</Th>
		<Th >creatorId</Th>
	</TR>
</THEAD>
<TBODY>
<?php

		foreach($usersToMerge as $userToMerge)
		{
			echo "
		<TR>
			<Th ><input type=\"radio\" name=\"uid_to_keep\" value=\"".$userToMerge['user_id']."\" ></Th>
			<Th ><input type=\"checkbox\" name=\"uid_to_merge[]\" checked=\"checked\" value=\"".$userToMerge['user_id']."\" ></Th>
			<Th >".$userToMerge['user_id']."</Th>
			<TD >".$userToMerge['officialCode']."</TD>
			<TD >".$userToMerge['prenom']."</TD>
			<TD >".$userToMerge['nom']."</TDN>
			<TD >".$userToMerge['username']."</TD>
			<TD >".$userToMerge['password']."</TD>
			<TD >".$userToMerge['email']."</TD>
			<TD >".$userToMerge['statut']."</TD>
			<TD >".$userToMerge['creatorId']."</TD>
		</TR>";
		}
?>
</TBODY>
</table>
<?php
	}
	else
	{
?>
	<a href="<?php echo $_SELF ?>?sniffTwice=1">Cherche des comptes suspects</a>
<?php
	}
?>
</fieldset>
	<fieldset style="width: 1*;float: left">
		<label for="uid_to_merge1" ><?php echo $lang_uid_to_add ?></label> :
	<input type="text" id="uid_to_merge1" name="uid_to_merge[]" size=6 maxlength=10><br>
	</fieldset>

	<fieldset style="width: 1*">
	ou par info user<br>
	<label for="official_to_add"><?php echo $lang_official_to_add ?></label> :
	<input type="text" id="official_to_add" name="official_to_add" >
	<br>
	<label for="name_to_add"><?php echo $lang_name_to_add ?></label> :
	<input type="text" id="name_to_add" name="name_to_add" >
	<br>
	<label for="firstname_to_add"><?php echo $lang_firstname_to_add ?></label> :
	<input type="text" id="firstname_to_add" name="firstname_to_add" >
	<br>
	<label for="username_to_add"><?php echo $lang_username_to_add ?></label> :
	<input type="text" id="username_to_add" name="username_to_add" >
	<br>
	<label for="email_to_add"><?php echo $lang_email_to_add ?></label> :
	<input type="text" id="email_to_add" name="email_to_add" >
	<br>
	</fieldset>
	<fieldset class="warning">
		<label for="run_merge" ><?php echo $lang_merge_proceed ?></label> :
		<input id="run_merge" type="checkbox" name="run_merge" value="do"> <span class="warning warn"><?php echo $lang_no_back ?></span>
	</fieldset>

	<input type="submit">
</form>

<?php
	echo "<PRE>";
}
//// FOOTER ////

include($includePath."/claro_init_footer.inc.php");
?>
