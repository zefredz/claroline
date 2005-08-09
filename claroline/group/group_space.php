<?php # $Id$
/** 
 * CLAROLINE 
 *
 * @version 1.7
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @see http://www.claroline.net/wiki/index.php/CLGRP
 *
 * @package CLGRP
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */

$tlabelReq = 'CLGRP___';
require '../inc/claro_init_global.inc.php';
$toolRepository = $clarolineRepositoryWeb;
if ( ! $_cid) claro_disp_select_course();

// block if !$_gid
// accept  if $is_groupAllowed

if (!$_gid || (!$is_groupAllowed & !isset($_REQUEST['selfReg']) ))
{
    header('Location:group.php');
}

$nameTools        = $_group['name'];

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
            header('Location:' . $_SERVER['PHP_SELF'] . '?gidReset=1&amp;gidReq=' . $_gid . '&amp;regDone=1');
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
		FROM `" . $tbl_user . "` `user`, `" . $tbl_group_rel_team_user . "` `user_group`
		WHERE `user_group`.`team`= '" . $_gid . "'
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
include($includePath . '/claro_init_header.inc.php');

echo claro_disp_tool_title('<img src="'.$imgRepositoryWeb.'group.gif" />' . $nameTools);

if ( !empty($message) )
{
    echo claro_disp_message_box($message);
}

if($is_allowedToSelfRegInGroup)
{
    echo '<p>'
    .    '<a href="' . $_SERVER['PHP_SELF'] . '?registration=1" class="claroCmd">'
    .    '<img src="' . $imgRepositoryWeb . 'enroll.gif" alt="' . $langRegIntoGroup . '" />'
    .    $langRegIntoGroup
    .    '</a>'
    .    '</p>'
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


$toolList = get_group_tool_list();
if (isset($_uid))
{
    $date = $claro_notifier->get_last_login_before_today($_uid);
    $modified_tools = $claro_notifier->get_notified_tools($_cid, $date, $_uid, $_gid);
}
else $modified_tools = array();

foreach($toolList as $thisTool)
{
    if ( ! empty($thisTool['label']))   // standart claroline tool
    {
        $toolName = $toolNameList[ $thisTool['label'] ];
        $url      = trim($toolRepository . $thisTool['url']);
    }
    elseif( ! empty($thisTool['name']) ) // external tool added by course manager
    {
        $toolName = $thisTool['name'];
        $url      = trim($thisTool['url']);
    }
    else
    {
        $toolName = '<i>no name</i>';
        $url      = trim($thisTool['url']);
    }

    if (! empty($thisTool['icon']))
    {
        $icon = $imgRepositoryWeb . $thisTool['icon'];
    }
    else
    {
        $icon = $imgRepositoryWeb . 'tool.gif';
    }

    /*    if ($accessLevelList[$thisTool['access']] > $accessLevelList['ALL'])
    {
    $style = 'invisible ';
    }
    else
    {
    $style = '';
    }
    */      $style = '';
    // see if tool name must be displayed in bold text or not

    if (in_array($thisTool['id'], $modified_tools))
    {
        $classItem = " hot";
    }
    else // otherwise just display its name normally
    {
        $classItem = "";
    }

    if ( ! empty($url) )
    {
        echo ' <a class="' . $style . ' item'.$classItem.'" href="' . $url . '">'
        .    '<img src="' . $icon . '" hspace="5" alt="">'
        .    $toolName
        .    '</a>'
        .    '<br>' . "\n"
        ;
    }
    else
    {
        echo '<span ' . $style . '>'
        .    '<img src="' . $icon . '" alt="">'
        .    $toolName
        .    '</span><br>' . "\n"
        ;
    }
}


// Drive members into their own File Manager


echo '<br /><br />';

if ($is_allowedToManage)
{
    echo '<a href="group_edit.php" class="claroCmd">'
    .    '<img src="' . $imgRepositoryWeb . 'edit.gif" alt="' . $langEditGroup . '" />'
    .    $langEditGroup
    .    '</a>'
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
        .    $thisTutor['lastName'] . ' ' . $thisTutor['firstName']
        .    ' - <a class="email" href="mailto:' . $thisTutor['email'] . '">'
        .    $thisTutor['email']
        .    '</a>'
        .    '</span>'
        .    '<br>'
        ;
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
        echo '<a href="../tracking/userLog.php?uInfo=' . $thisGroupMember['id'] . '" class="item">'
        .    $thisGroupMember['lastName'] . ' ' . $thisGroupMember['firstName']
        .    '</a> - '
        .    '<a href="mailto:' . $thisGroupMember['email'] . '">'
        .    $thisGroupMember['email']
        .    '</a>'
        .    '<br>'
        ;
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


/**
 * This dirty function is a blackbox to provide normalised output of tool list for a group
 * like  get_course_tool_list($course_id=NULL) in course_home.
 *
 * It's dirty because data structure is dirty.
 * Tool_list (with clarolabel and tid come from tool tables and  group properties and localinit)
 * @author Christophe Gesché <moosh@claroline.net>
 * @return array
 */


function get_group_tool_list($course_id=NULL)
{
    global $_groupProperties,
    $is_courseAdmin, $is_groupMember, $is_groupTutor, $forumId;

    $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_course_tool = $tbl_cdb_names['tool'];

    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_tool  = $tbl_mdb_names['tool'];

    $aivailable_tool_in_group = array('CLFRM','CLCHT','CLDOC','CLWIKI');

    $is_allowedToDocAccess      = (bool) (   $is_courseAdmin
|| $is_groupMember
|| $is_groupTutor);

$is_allowedToChatAccess     = (bool) ( 	$is_courseAdmin
|| $is_groupMember
|| $is_groupTutor );

    
    $sql = "
SELECT tl.id                               id,
       tl.script_name                      name,
       tl.access                           access,
       tl.rank                             rank,
       IFNULL(ct.script_url,tl.script_url) url,
       ct.claro_label                      label,
       ct.icon                             icon
FROM      `" . $tbl_course_tool . "`       tl
LEFT JOIN `" . $tbl_tool . "` `ct` 
ON        ct.id = tl.tool_id";

    $tool_list = claro_sql_query_fetch_all($sql);
    
    foreach($tool_list as $tool)
    {
        if (in_array(trim($tool['label'],'_'),$aivailable_tool_in_group))
        {
            switch (trim($tool['label'],'_'))
            {
                case 'CLDOC' :
                {
                    if($_groupProperties['tools']['document'] && $is_allowedToDocAccess)
                    {
                        $group_tool_list[] = $tool;
                    }
                } break;

                case 'CLFRM' :
                {
                    if($_groupProperties['tools']['forum'])
                    {
                        $tool['url'] = 'phpbb/viewforum.php?forum=' . $forumId ;
                        $group_tool_list[] = $tool;
                    }

                } break;

                case 'CLWIKI' :
                {
                    if($_groupProperties['tools']['wiki'])
                    {
                        $group_tool_list[] = $tool;
                    }
                } break;

                case 'CLCHT' :
                {
                    if($_groupProperties['tools']['chat'] && $is_allowedToChatAccess)
                    {
                        $group_tool_list[] = $tool;
                    }
                }break;

            }
        }
    }

    return $group_tool_list;
}
?>