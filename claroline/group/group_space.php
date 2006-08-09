<?php // $Id$
/**
 * CLAROLINE
 *
 * This tool is "groupe_home" + "group_user"
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
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

$cidNeeded = true;
$gidNeeded = true;
$tlabelReq = 'CLGRP';

require '../inc/claro_init_global.inc.php';
include_once $includePath . '/lib/group.lib.inc.php';

$toolNameList= claro_get_tool_name_list();
$toolRepository = $clarolineRepositoryWeb;

if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);

// block if !$_gid
// accept  if $is_groupAllowed

if ( ! $_gid
    || (!   $is_groupAllowed
        && !isset($_REQUEST['selfReg'])
        || ( isset($_REQUEST['registration']) && $_REQUEST['registration'] != 1 ) ))
{
    claro_redirect('group.php');
    exit();
}

// use viewMode
claro_set_display_mode_available(true);

/********************
* CONNECTION SECTION
*********************/

$is_courseMember     = $is_courseMember;
$is_groupMember      = $is_groupMember;
$is_allowedToManage  = claro_is_allowed_to_edit();
/*
* DB tables definition
*/

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_rel_course_user         = $tbl_mdb_names['rel_course_user'];
$tbl_user                    = $tbl_mdb_names['user'];
$tbl_bb_forum                = $tbl_cdb_names['bb_forums'];
$tbl_group_rel_team_user     = $tbl_cdb_names['group_rel_team_user'];
$tbl_group_team              = $tbl_cdb_names['group_team'];
/****************************************************************************/


// COUNT IN HOW MANY GROUPS CURRENT USER ARE IN
// (needed to give or refuse selfreg right)

$groupMemberCount = group_count_students_in_group($_gid);

$groupMemberQuotaExceeded = (bool) ( ! is_null($_group['maxMember']) && ($_group ['maxMember'] <= $groupMemberCount) ); // no limit assign to group per user;

$userGroupRegCount = group_count_group_of_a_user($_uid);

// The previous request compute the quantity of subscription for the current user.
// the following request compare with the quota of subscription allowed to each student
$userGroupQuotaExceeded = (bool) (   $_groupProperties ['nbGroupPerUser'] <= $userGroupRegCount)
&& ! is_null($_groupProperties['nbGroupPerUser']); // no limit assign to group per user;

$is_allowedToSelfRegInGroup = (bool) ( $_groupProperties ['registrationAllowed']
&& ( ! $groupMemberQuotaExceeded )
&& ( ! $userGroupQuotaExceeded )
&& ( ! $is_courseTutor ||
     ( $is_courseTutor
       &&
       get_conf('tutorCanBeSimpleMemberOfOthersGroupsAsStudent')
       )));

$is_allowedToSelfRegInGroup  = (bool) $is_allowedToSelfRegInGroup && $_uid && ( ! $is_groupMember ) && $is_courseMember;



$is_allowedToDocAccess = (bool) ( $is_courseAdmin || $is_groupMember || $is_groupTutor);
$is_allowedToChatAccess     = (bool) (     $is_courseAdmin || $is_groupMember || $is_groupTutor );

/**
 * SELF-REGISTRATION PROCESS
 */

if( isset($_REQUEST['registration']) )
{
    //RECHECK if subscribe is aivailable
    if( $is_courseMember &&  ! $is_groupMember && $is_allowedToSelfRegInGroup)
    {
        if( isset($_REQUEST['doReg']) )
        {
            //RECHECK if subscribe is aivailable
            if( $is_courseMember &&  ! $is_groupMember && $is_allowedToSelfRegInGroup)
            {

                $sql = "INSERT INTO `" . $tbl_group_rel_team_user . "`
                SET `user` = " . (int) $_uid . ",
                    `team` = " . (int) $_gid ;
                if (claro_sql_query($sql))
                {
                    // REFRESH THE SCRIPT TO COMPUTE NEW PERMISSIONS ON THE BASSIS OF THIS CHANGE
                    claro_redirect($_SERVER['PHP_SELF'] . '?gidReset=1&gidReq=' . $_gid . '&regDone=1');
                    exit();

                }
            }
        }
        else // Confirm reg
        {
            $message = get_lang('Confirm your subscription to the group &quot;<b>%group_name</b>&quot;',array('%group_name'=>$_group['name'])) . "\n"
            .          '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">' . "\n"
            .          '<input type="hidden" name="registration" value="1">' . "\n"
            .          '<input type="hidden" name="doReg" value="1">' . "\n"
            .          '<br />' . "\n"
            .          '<input type="submit" value="' . get_lang("Ok") . '">' . "\n"
            .          claro_html_button($_SERVER['PHP_SELF'] , get_lang("Cancel")) . "\n"
            .          '</form>' . "\n"
            ;



        }



    }
}


if ( isset($_REQUEST['regDone']) )
{
    $message = get_lang("You are now a member of this group.");
}


/********************************
 * GROUP INFORMATIONS RETRIVIAL
 ********************************/


/*----------------------------------------------------------------------------
GET GROUP MEMBER LIST
----------------------------------------------------------------------------*/

$sql = "SELECT `user_id` AS `id`, `nom` AS `lastName`, `prenom` AS `firstName`, `email`
        FROM `" . $tbl_user . "` `user`, `" . $tbl_group_rel_team_user . "` `user_group`
        WHERE `user_group`.`team`= '" . $_gid . "'
        AND   `user_group`.`user`= `user`.`user_id`";

$groupMemberList = claro_sql_query_fetch_all($sql);


/*----------------------------------------------------------------------------
GET TUTOR(S) DATA
----------------------------------------------------------------------------*/

$sql = "SELECT user_id AS id, nom AS lastName, prenom AS firstName, email
        FROM `".$tbl_user."` user
        WHERE user.user_id='".$_group['tutorId']."'";

$tutorDataList = claro_sql_query_fetch_all($sql);

/*----------------------------------------------------------------------------
GET FORUM POINTER
----------------------------------------------------------------------------*/
$forumId = $_group['forumId'];

$toolList = get_group_tool_list();

if (isset($_uid))
{
    $date = $claro_notifier->get_notification_date($_uid);
    $modified_tools = $claro_notifier->get_notified_tools($_cid, $date, $_uid, $_gid);
}
else $modified_tools = array();

$toolLinkList = array();

foreach($toolList as $thisTool)
{
    // special case when display mode is student and tool invisible doesn't display it
    if ( ( claro_get_tool_view_mode() == 'STUDENT' ) && ! $thisTool['visibility']  )
    {
        continue;
    }


    if ( ! empty($thisTool['label']))   // standart claroline tool
    {
        $toolName = get_lang( $toolNameList[ $thisTool['label'] ] );
        $url      = trim(get_module_url($thisTool['label']) . '/' . $thisTool['url']);
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

    $style = '';

    // patchy
    if ( $is_platformAdmin || $is_courseAdmin )
    {
        if(!array_key_exists($thisTool['label'],$_groupProperties['tools']) || !$_groupProperties['tools'][$thisTool['label']])
        {
            $style = 'invisible ';
        }
    }

    /*    if ($accessLevelList[$thisTool['access']] > $accessLevelList['ALL'])
    {
    $style = 'invisible ';
    }
    else
    {
    $style = '';
    }
    */
    // see if tool name must be displayed 'as containing new items' (a red ball by default)  or not
    $classItem = '';
    if (in_array($thisTool['id'], $modified_tools)) $classItem = " hot";

    if ( ! empty($url) )
    {
        $toolLinkList[] = '<a class="' . $style . ' item' . $classItem . '" href="' . $url . '">'
        .                 '<img src="' . $icon . '" alt="" />'
        .                 $toolName
        .                 '</a>' . "\n"
        ;
    }
    else
    {
        $toolLinkList[] = '<span ' . $style . '>'
        .                 '<img src="' . $icon . '" alt="" />'
        .                 $toolName
        .                 '</span>' . "\n"
        ;
    }
}


/*****************
 * DISPLAY SECTION
 ******************/

// CLAROLINE HEADER AND BANNER
include($includePath . '/claro_init_header.inc.php');

echo claro_html_tool_title( array('supraTitle'=> get_lang("Groups"),
                                  'mainTitle' => $_group['name'] . ' <img src="'.$imgRepositoryWeb.'group.gif" alt="" />'));

if ( !empty($message) )
{
    echo claro_html_message_box($message);
}


if($is_allowedToSelfRegInGroup && !array_key_exists('registration',$_REQUEST))
{
    echo '<p>' . "\n"
    .    '<a href="' . $_SERVER['PHP_SELF'] . '?registration=1" class="claroCmd">'
    .    '<img src="' . $imgRepositoryWeb . 'enroll.gif" alt="' . get_lang("Add me to this group") . '" />'
    .    get_lang("Add me to this group")
    .    '</a>' . "\n"
    .    '</p>'
    ;
}


echo '<p></p><table cellpadding="5" cellspacing="0" border="0">'  . "\n"
.    '<tr>'  . "\n"
.    '<td style="border-right: 1px solid gray;" valign="top" width="220">'  . "\n"

/*
* Vars needed to determine group File Manager and group Forum
* They are unregistered when opening group.php once again.
*
* session_register("secretDirectory");
* session_register("userGroupId");
* session_register("forumId");
*/

.   claro_html_menu_vertical_br($toolLinkList)
.   '<br /><br />' . "\n"
;

if ($is_allowedToManage)
{
    echo '<a href="group_edit.php" class="claroCmd">'
    .    '<img src="' . $imgRepositoryWeb . 'edit.gif" alt="' . get_lang("Edit this group") . '" />'
    .    get_lang("Edit this group")
    .    '</a>'
    ;
}

echo '</td>' . "\n"
.    '<td width="20">' . "\n"
.    '&nbsp;' . "\n"
.    '</td>' . "\n"
.    '<td valign="top">' . "\n"
.    '<b>' . "\n"
.    get_lang("Description") . "\n"
.    '</b> :' . "\n"
;

/*----------------------------------------------------------------------------
DISPLAY GROUP DESCRIPTION
----------------------------------------------------------------------------*/

if( strlen($_group['description']) > 0)
{
    echo '<br /><br />' . "\n"
    .    $_group['description']
    ;
}
else // Show 'none' if no description
{
    echo get_lang("(none)");
}

echo '<br /><br />'
.    '<b>'
.    get_lang("Group Tutor")
.    '</b> :'
;

/*----------------------------------------------------------------------------
DISPLAY GROUP TUTOR INFORMATION
----------------------------------------------------------------------------*/

if (count($tutorDataList) > 0)
{
    echo '<br /><br />' . "\n";
    foreach($tutorDataList as $thisTutor)
    {
        echo '<span class="item">'
        .    $thisTutor['lastName'] . ' ' . $thisTutor['firstName']
        .    ' - <a class="email" href="mailto:' . $thisTutor['email'] . '">'
        .    $thisTutor['email']
        .    '</a>'
        .    '</span>'
        .    '<br />'
        ;
    }
}
else
{
    echo get_lang("(none)");
}
?>

<br /><br />

<b><?php echo get_lang("Group members") ?></b> :
<?php


/*----------------------------------------------------------------------------
DISPLAY GROUP MEMBER LIST
----------------------------------------------------------------------------*/

if(count($groupMemberList) > 0)
{
    echo '<br /><br />' . "\n";
    foreach($groupMemberList as $thisGroupMember)
    {
        echo '<a href="../tracking/userLog.php?uInfo=' . $thisGroupMember['id'] . '" class="item">'
        .    $thisGroupMember['lastName'] . ' ' . $thisGroupMember['firstName']
        .    '</a> - '
        .    '<a href="mailto:' . $thisGroupMember['email'] . '">'
        .    $thisGroupMember['email']
        .    '</a>'
        .    '<br />' . "\n"
        ;
    }
}
else
{
    echo get_lang('(none)');
}


echo '</td>' . "\n"
.    '</tr>' . "\n"
.    '</table>' . "\n"
;

include $includePath . '/claro_init_footer.inc.php';


?>
