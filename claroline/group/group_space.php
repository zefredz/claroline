<?php # $Id$

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

$langFile = 'group';
require '../inc/claro_init_global.inc.php';
include('../inc/conf/group.conf.php');
if ( ! $_cid) claro_disp_select_course();

// block if !$_gid
// accept  if $is_groupAllowed

if (!$_gid || (!$is_groupAllowed & !($HTTP_GET_VARS['selfReg'] ) ))
{
   header('Location:group.php');
}


$nameTools        = $langGroupSpace;
$interbredcrump[] = array ('url'=>'group.php', 'name'=> $langGroupManagement);

$htmlHeadXtra[]= "
<style type=\"text/css\">
<!--
.select {border-color:blue;border-width : 3px;}
.box {  width: 200px}
-->
</style>";

/*============================================================================
                               CONNECTION SECTION
  ============================================================================*/


$is_courseMember     = $is_courseMember;
$is_groupMember      = $is_groupMember;
$is_allowedToManage  = $is_courseAdmin;

$TABLEUSER            = $mainDbName."`.`user";
$TABLECOURSUSER       = $mainDbName."`.`cours_user";

$TABLEGROUP           = $_course['dbNameGlu'].'group_team';
$TABLEGROUPPROPERTIES = $_course['dbNameGlu'].'group_property';
$TABLEUSERGROUP       = $_course['dbNameGlu'].'group_rel_team_user';
$TABLEFORUM           = $_course['dbNameGlu'].'bb_forums';
/*========================================================================*/


// COUNT IN HOW MANY GROUPS CURRENT USER ARE IN
// (needed to give or refuse selfreg right)

$sql = "SELECT COUNT(id) qtyMember
        FROM `".$TABLEUSERGROUP."`
        WHERE `team` = '".$_gid."'";

list($result) = claro_sql_query_fetch_all($sql);

$groupMemberQuotaExceeded = (bool) (   $_group ['maxMember'] <= $result['qtyMember'])
                                  || is_null($_group['maxMember']); // no limit assign to group per user;
$sql = "SELECT COUNT(team) userGroupRegCount
        FROM `".$TABLEUSERGROUP."`
        WHERE `user` = '".$_uid."'";

list($result) = claro_sql_query_fetch_all($sql);

$userGroupQuotaExceeded = (bool) (   $_groupProperties ['nbGroupPerUser'] <= $result['userGroupRegCount'])
                                  && ! is_null($_groupProperties['nbGroupPerUser']); // no limit assign to group per user;

$is_allowedToSelfRegInGroup = (bool) (     $_groupProperties ['registrationAllowed']
                                      && ( ! $groupMemberQuotaExceeded )
                                      && ( ! $userGroupQuotaExceeded )
                                      && ( ! $is_courseTutor
                                           || ($is_courseTutor
                                               && $tutorCanBeSimpleMemberOfOthersGroupsAsStudent)));

$is_allowedToSelfRegInGroup  = (bool)    $is_allowedToSelfRegInGroup
                                      && $_uid
                                      && ( ! $is_groupMember )
                                      && $is_courseMember;


$is_allowedToDocAccess      = (bool) (   $is_courseAdmin
                                      || $is_groupMember
                                      || $is_groupTutor);

$is_allowedToChatAccess     = (bool) ( 	$is_courseAdmin
					|| $is_groupMember 
					|| $is_groupTutor );



/*============================================================================
                           SELF-REGISTRATION PROCESS
============================================================================*/
if($_REQUEST['registration'])
{
    if( $is_courseMember &&  ! $is_groupMember && $is_allowedToSelfRegInGroup)
    {
		//RECHECK if subscribe is aivailable

        $sql = 'INSERT INTO `'.$TABLEUSERGROUP.'`
                SET user = \''.$_uid.'\',
                    team = \''.$_gid.'\'';
        
	 	if (claro_sql_query($sql))
        {
            // REFRESH THE SCRIPT TO COMPUTE NEW PERMISSIONS ON THE BASSIS OF THIS CHANGE
            header("Location:".$PHP_SELF."?gidReset=1&gidReq=".$_gid."&regDone=1");
        }
    }
}

if ($_GET['regDone'])
{
    $message = $langGroupNowMember;
}


/*============================================================================
                          GROUP INFORMATIONS RETRIVIAL
  ============================================================================*/


/*----------------------------------------------------------------------------
                             GET GROUP MEMBER LIST
  ----------------------------------------------------------------------------*/

$sql = "SELECT user_id id, nom lastName, prenom firstName, email
		FROM `".$TABLEUSER."` user, `".$TABLEUSERGROUP."` user_group
		WHERE user_group.team='$_gid'
		AND   user_group.user= user.user_id";

$groupMemberList = claro_sql_query_fetch_all($sql);


/*----------------------------------------------------------------------------
                               GET TUTOR(S) DATA
  ----------------------------------------------------------------------------*/

$sql = "SELECT user_id id, nom lastName, prenom firstName, email
        FROM `".$TABLEUSER."` user
        WHERE user.user_id=\"".$_group['tutorId']."\"";

$tutorDataList = claro_sql_query_fetch_all($sql);


/*----------------------------------------------------------------------------
                               GET FORUM POINTER
  ----------------------------------------------------------------------------*/

$forumId = $_group['forumId'];



/*============================================================================
                                DISPLAY SECTION
  ============================================================================*/

// CLAROLINE HEADER AND BANNER
include($includePath.'/claro_init_header.inc.php');

claro_disp_tool_title($nameTools);

if($message)
{
    claro_disp_message_box($message);
}

?>

<table cellpadding="5" cellspacing="0" border="0">

<tr valign="top">

<td align="right"><?php echo $langGroupName ?> : </td>
<td><b><?php echo $_group['name'] ?></b><td>

</tr>

<tr valign="top">

<td align="right"><?php echo $langGroupDescription ?> : </td>
<td>
<?php

 /*----------------------------------------------------------------------------
                           DISPLAY GROUP DESCRIPTION
 ----------------------------------------------------------------------------*/

if( strlen($_group['description']) > 0)
{
	echo $_group['description'];
}
else // Show 'none' if no description
{
	echo $langGroupNone;
}

?>
</td>

</tr>


<tr valign="top">

<td align="right"><?php echo $langGroupTutor ?> : </td>
<td>
<?php

/*----------------------------------------------------------------------------
                        DISPLAY GROUP TUTOR INFORMATION
  ----------------------------------------------------------------------------*/

$sql = "SELECT user_id id, nom lastName, prenom firstName, email
        FROM `".$TABLEUSER."` user
        WHERE user.user_id=\"".$_group['tutorId']."\"";

$tutorDataList = claro_sql_query_fetch_all($sql);

if (count($tutorDataList) > 0)
{
    foreach($tutorDataList as $thisTutor)
    {
        echo $thisTutor['lastName']." ".$thisTutor['firstName']
            ." <a href=\"mailto:".$thisTutor['email']."\">"
            .$thisTutor['email']
            ."</a>"
            ."<br>";
	}
}
else
{
	echo $langGroupNoTutor;
}
?>
</td>

</tr>

<tr valign="top">

<td align="right"><?php echo $langTools ?> : </td>
<td>
<?php

 /*----------------------------------------------------------------------------
                          DISPLAY AVAILABLE TOOL LIST
 ----------------------------------------------------------------------------*/


/*
 * Vars needed to determine group File Manager and group Forum
 * They are unregistered when opening group.php once again.
 *
 * session_register("secretDirectory");
 * session_register("userGroupId");
 * session_register("forumId");
 */
if($_groupProperties['tools']['forum'])
{
    echo "<a href=\"../phpbb/viewforum.php?forum=".$forumId."\">"
        .$langForums
        ."</a>"
        ."<br>";
}

// Drive members into their own File Manager
if($_groupProperties['tools']['document'] && $is_allowedToDocAccess)
{
    echo "<a href=\"../document/document.php\">".$langDocuments."</a><br>";
}

if($_groupProperties['tools']['wiki'])
{
    echo "<a href=\"../wiki/wiki.php\">".$langWiki."</a><br>";
}

if($_groupProperties['tools']['chat'] && $is_allowedToChatAccess)
{
  echo "<a href=\"../chat/chat.php?gidReq=".$_gid."\">".$langChat."</a><br>";
}


?>
</td>
</tr>
<tr valign="top">
<td align="right"><?php echo  $langGroupMembers ?> : </td>
<td>
<?php


 /*----------------------------------------------------------------------------
                           DISPLAY GROUP MEMBER LIST
 ----------------------------------------------------------------------------*/

if(count($groupMemberList) > 0)
{
    foreach($groupMemberList as $thisGroupMember)
    {
        echo "<a href=\"../tracking/userLog.php?uInfo=".$thisGroupMember['id']."\">"
            .$thisGroupMember['lastName'].' '.$thisGroupMember['firstName']
            ."</a> -"
            ."<a href=\"mailto:".$thisGroupMember['email']."\">"
            .$thisGroupMember['email']
            ."</a>"
            ."<br>";
    }
}
else
{
    echo $langGroupNoneMasc;
}

?>
</td>

</tr>

<?php

if ($is_allowedToManage)
{ 
    echo "<tr valign=\"top\">"
        ."<td>&nbsp;</td>"
        ."<td>"
        ."<form method=\"get\" action=\"group_edit.php\">"
        ."<input type=\"submit\" value=\"".$langEditGroup."\">"
        ."</form>"
        ."</td>"
        ."</tr>";
}

if($is_allowedToSelfRegInGroup)
{
    echo "<form method=\"post\" action=\"".$PHP_SELF."?\">"
	."<input type=\"hidden\" name=\"registration\" value=\"1\">"
        ."<input type=\"submit\" value=\"".$langRegIntoGroup."\">"
        ."</form>";
}

?>
</table>
<?php
include($includePath.'/claro_init_footer.inc.php');
?>
