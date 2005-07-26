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

$tlabelReq = "CLGRP___";
require '../inc/claro_init_global.inc.php';

if ( ! $_cid) claro_disp_select_course();

// block if !$_gid
// accept  if $is_groupAllowed

if (!$_gid || (!$is_groupAllowed & !isset($_REQUEST['selfReg']) ))
{
   header('Location:group.php');
}

$nameTools        = $langGroupSpace;
$interbredcrump[] = array ('url'=>'group.php', 'name'=> $langGroups);

// use viewMode
claro_set_display_mode_available(true);

/*============================================================================
                               CONNECTION SECTION
  ============================================================================*/

$is_courseMember     = $is_courseMember;
$is_groupMember      = $is_groupMember;
$is_allowedToManage  = claro_is_allowed_to_edit(); 
/*
 * DB tables definition
 */

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_rel_course_user         = $tbl_mdb_names['rel_course_user'  ];
$tbl_user                    = $tbl_mdb_names['user'             ];
$tbl_bb_forum                = $tbl_cdb_names['bb_forums'             ];
$tbl_course_group_property   = $tbl_cdb_names['group_property'         ];
$tbl_group_rel_team_user     = $tbl_cdb_names['group_rel_team_user'    ];
$tbl_group_team              = $tbl_cdb_names['group_team'             ];
/*========================================================================*/


// COUNT IN HOW MANY GROUPS CURRENT USER ARE IN
// (needed to give or refuse selfreg right)

$sql = "SELECT COUNT(id) qtyMember
        FROM `".$tbl_group_rel_team_user."`
        WHERE `team` = '".$_gid."'";

list($result) = claro_sql_query_fetch_all($sql);

$groupMemberQuotaExceeded = (bool) (   $_group ['maxMember'] <= $result['qtyMember'])
                                  || is_null($_group['maxMember']); // no limit assign to group per user;
$sql = "SELECT COUNT(team) userGroupRegCount
        FROM `".$tbl_group_rel_team_user."`
        WHERE `user` = '".$_uid."'";

list($result) = claro_sql_query_fetch_all($sql);

// The previous request compute the quantity of subscription for the current user.
// the following request compare with the quota of subscription allowed to each student
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

if( isset($_REQUEST['registration']) )
{
    if( $is_courseMember &&  ! $is_groupMember && $is_allowedToSelfRegInGroup)
    {
		//RECHECK if subscribe is aivailable
        $sql = 'INSERT INTO `'.$tbl_group_rel_team_user.'`
                SET `user` = "'.$_uid.'",
                    `team` = "'.$_gid.'"';

        if (claro_sql_query($sql))
        {
            // REFRESH THE SCRIPT TO COMPUTE NEW PERMISSIONS ON THE BASSIS OF THIS CHANGE
            header("Location:".$_SERVER['PHP_SELF']."?gidReset=1&amp;gidReq=".$_gid."&amp;regDone=1");
        }
    }
}

if ( isset($_REQUEST['regDone']) )
{
    $message = $langGroupNowMember;
}


/*============================================================================
                          GROUP INFORMATIONS RETRIVIAL
  ============================================================================*/


/*----------------------------------------------------------------------------
                             GET GROUP MEMBER LIST
  ----------------------------------------------------------------------------*/

$sql = "SELECT `user_id` `id`, `nom` `lastName`, `prenom` `firstName`, `email`
		FROM `".$tbl_user."` `user`, `".$tbl_group_rel_team_user."` `user_group`
		WHERE `user_group`.`team`= '".$_gid."'
		AND   `user_group`.`user`= `user`.`user_id`";

$groupMemberList = claro_sql_query_fetch_all($sql);


/*----------------------------------------------------------------------------
                               GET TUTOR(S) DATA
  ----------------------------------------------------------------------------*/

$sql = "SELECT user_id id, nom lastName, prenom firstName, email
        FROM `".$tbl_user."` user
        WHERE user.user_id='".$_group['tutorId']."'";

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

echo claro_disp_tool_title(array('mainTitle' => $_group['name'],
'subTitle' => '<img src="'.$imgRepositoryWeb.'group.gif" />' . $nameTools,
'supraTitle' => $langGroups));

if ( !empty($message) )
{
    echo claro_disp_message_box($message);
}

if($is_allowedToSelfRegInGroup)
{
    echo '<p>'
        . '<a href="'.$_SERVER['PHP_SELF'].'?registration=1" class="claroCmd">'
        .'<img src="'.$imgRepositoryWeb.'enroll.gif" alt="'.$langRegIntoGroup.'" />'
        .$langRegIntoGroup
        .'</a>'
        . '</p>'
        ;
}

?>

<table cellpadding="5" cellspacing="0" border="0">
<tr>
<td style="border-right: 1px solid gray;" valign="top" width="220">
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
    echo "<a href=\"../phpbb/viewforum.php?forum=".$forumId."\" class=\"item\">"
        .'<img src="'.$imgRepositoryWeb.'forum.gif" />'
        .'&nbsp;' .$langForum
        ."</a>"
        ."<br>"
        ;
}

// Drive members into their own File Manager
if($_groupProperties['tools']['document'] && $is_allowedToDocAccess)
{
    echo "<a href=\"../document/document.php\" class=\"item\">"
        .'<img src="'.$imgRepositoryWeb.'document.gif" />'
        .'&nbsp;' .$langDocuments
        ."</a><br>"
        ;
}

if($_groupProperties['tools']['wiki'])
{
    echo "<a href=\"../wiki/wiki.php\" class=\"item\">"
        .'<img src="'.$imgRepositoryWeb.'wiki.gif" />'
        .'&nbsp;' . $langWiki
        ."</a><br>"
        ;
}

if($_groupProperties['tools']['chat'] && $is_allowedToChatAccess)
{
  echo "<a href=\"../chat/chat.php?gidReq=".$_gid."\" class=\"item\">"
    .'<img src="'.$imgRepositoryWeb.'chat.gif" />'
    .'&nbsp;' .$langChat
    ."</a><br>"
    ;
}

echo '<br /><br />';

if ($is_allowedToManage)
{
    echo '<a href="group_edit.php" class="claroCmd">'
        .'<img src="'.$imgRepositoryWeb.'edit.gif" alt="'.$langEditGroup.'" />'
        .$langEditGroup
        .'</a>'
        ;
}


?>
</td>
<td width="20">
&nbsp;
</td>
<td valign="top">
<b><?php echo $langGroupDescription ?></b> :
<?php

 /*----------------------------------------------------------------------------
                           DISPLAY GROUP DESCRIPTION
 ----------------------------------------------------------------------------*/

if( strlen($_group['description']) > 0)
{
    echo '<br /><br />';
	echo $_group['description'];
}
else // Show 'none' if no description
{
    echo $langGroupNone;
}

?>

<br /><br />


<b><?php echo $langGroupTutor ?></b> :
<?php

/*----------------------------------------------------------------------------
                        DISPLAY GROUP TUTOR INFORMATION
  ----------------------------------------------------------------------------*/

if (count($tutorDataList) > 0)
{
    echo '<br /><br />';
    foreach($tutorDataList as $thisTutor)
    {
        echo '<span class="item">'
            . $thisTutor['lastName'].' '.$thisTutor['firstName']
            .' - <a class="email" href="mailto:'.$thisTutor['email'].'">'
            .$thisTutor['email']
            .'</a>'
            .'</span>'
            .'<br>';
	}
}
else
{
	echo $langGroupNoTutor;
}
?>
<br /><br />

<b><?php echo  $langGroupMembers ?></b> :
<?php


 /*----------------------------------------------------------------------------
                           DISPLAY GROUP MEMBER LIST
 ----------------------------------------------------------------------------*/

if(count($groupMemberList) > 0)
{
    echo '<br /><br />';
    foreach($groupMemberList as $thisGroupMember)
    {
        echo "<a href=\"../tracking/userLog.php?uInfo=".$thisGroupMember['id']."\" class=\"item\">"
            .$thisGroupMember['lastName'].' '.$thisGroupMember['firstName']
            ."</a> - "
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
</td></tr>
</table>
<?php
include($includePath.'/claro_init_footer.inc.php');
?>
