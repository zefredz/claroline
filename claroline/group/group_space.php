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
include_once get_path('incRepositorySys') . '/lib/group.lib.inc.php';

$toolNameList= claro_get_tool_name_list();
$toolRepository = get_path('clarolineRepositoryWeb');

if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);

// block if !claro_is_in_a_group()
// accept  if claro_is_group_allowed()

if ( ! claro_is_allowed_to_edit() )
{
    if ( ! claro_is_in_a_group() )
    {
        claro_redirect('group.php');
        exit();
    }
    elseif ( ! claro_is_group_allowed() && ! ( isset( $_REQUEST['selfReg'] ) || isset($_REQUEST['doReg']) ) )
    {
        claro_redirect('group.php');
        exit();
    }
}

// use viewMode
claro_set_display_mode_available(true);

/********************
* CONNECTION SECTION
*********************/

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

$_groupProperties = claro_get_current_group_properties_data();
// COUNT IN HOW MANY GROUPS CURRENT USER ARE IN
// (needed to give or refuse selfreg right)

$groupMemberCount = group_count_students_in_group(claro_get_current_group_id());

$groupMemberQuotaExceeded = (bool) ( ! is_null(claro_get_current_group_data('maxMember')) && (claro_get_current_group_data('maxMember') <= $groupMemberCount) ); // no limit assign to group per user;

$userGroupRegCount = group_count_group_of_a_user(claro_get_current_user_id());

// The previous request compute the quantity of subscription for the current user.
// the following request compare with the quota of subscription allowed to each student

$userGroupQuotaExceeded = (bool) (   $_groupProperties ['nbGroupPerUser'] <= $userGroupRegCount)
&& ! ( 0 === $_groupProperties['nbGroupPerUser'] ); // no limit assign to group per user;

$is_allowedToSelfRegInGroup = (bool) ( $_groupProperties ['registrationAllowed']
&& ( ! $groupMemberQuotaExceeded )
&& ( ! $userGroupQuotaExceeded )
&& ( ! claro_is_course_tutor() ||
     ( claro_is_course_tutor()
       &&
       get_conf('tutorCanBeSimpleMemberOfOthersGroupsAsStudent')
       )));

$is_allowedToSelfRegInGroup  = (bool) $is_allowedToSelfRegInGroup && claro_is_in_a_course() && ( ! claro_is_group_member() ) && claro_is_course_member();



$is_allowedToDocAccess = (bool) ( claro_is_course_manager() || claro_is_group_member() ||  claro_is_group_tutor());
$is_allowedToChatAccess     = (bool) (     claro_is_course_manager() || claro_is_group_member() ||  claro_is_group_tutor() );

/**
 * SELF-REGISTRATION PROCESS
 */

if( isset($_REQUEST['registration']) )
{
    //RECHECK if subscribe is aivailable
    if( claro_is_course_member() &&  ! claro_is_group_member() && $is_allowedToSelfRegInGroup)
    {
        if( isset($_REQUEST['doReg']) )
        {
            //RECHECK if subscribe is aivailable
            if( claro_is_course_member() &&  ! claro_is_group_member() && $is_allowedToSelfRegInGroup)
            {

                $sql = "INSERT INTO `" . $tbl_group_rel_team_user . "`
                SET `user` = " . (int) claro_get_current_user_id() . ",
                    `team` = " . (int) claro_get_current_group_id() ;
                if (claro_sql_query($sql))
                {
                    // REFRESH THE SCRIPT TO COMPUTE NEW PERMISSIONS ON THE BASSIS OF THIS CHANGE
                    claro_redirect($_SERVER['PHP_SELF'] . '?gidReset=1&gidReq=' . claro_get_current_group_id() . '&regDone=1');
                    exit();

                }
            }
        }
        else // Confirm reg
        {
            $message = get_lang('Confirm your subscription to the group &quot;<b>%group_name</b>&quot;',array('%group_name'=>claro_get_current_group_data('name'))) . "\n"
            .          '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">' . "\n"
            .          claro_form_relay_context()
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
        WHERE `user_group`.`team`= '" . claro_get_current_group_id() . "'
        AND   `user_group`.`user`= `user`.`user_id`";

$groupMemberList = claro_sql_query_fetch_all($sql);


/*----------------------------------------------------------------------------
GET TUTOR(S) DATA
----------------------------------------------------------------------------*/

$sql = "SELECT user_id AS id, nom AS lastName, prenom AS firstName, email
        FROM `".$tbl_user."` user
        WHERE user.user_id='".claro_get_current_group_data('tutorId')."'";

$tutorDataList = claro_sql_query_fetch_all($sql);

/*----------------------------------------------------------------------------
GET FORUM POINTER
----------------------------------------------------------------------------*/
$forumId = claro_get_current_group_data('forumId');

$toolList = get_group_tool_list();

if (claro_is_in_a_course())
{
    $date = $claro_notifier->get_notification_date(claro_get_current_user_id());
    $modified_tools = $claro_notifier->get_notified_tools(claro_get_current_course_id(), $date, claro_get_current_user_id(), claro_get_current_group_id());
}
else $modified_tools = array();

$toolLinkList = array();

foreach($toolList as $thisTool)
{
    // special case when display mode is student and tool invisible doesn't display it
    if ( !claro_is_allowed_to_edit() )
    {
        if(!array_key_exists($thisTool['label'],$_groupProperties['tools']) || !$_groupProperties['tools'][$thisTool['label']])
        {
            continue;
        }
    }


    if ( ! empty($thisTool['label']))   // standart claroline tool
    {
        $label = $toolNameList[$thisTool['label']] ;
        $toolName = get_lang($label);
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
        $icon = get_path('imgRepositoryWeb') . $thisTool['icon'];
    }
    else
    {
        $icon = get_path('imgRepositoryWeb') . 'tool.gif';
    }

    $style = '';

    // patchy
    if ( claro_is_platform_admin() || claro_is_course_manager() )
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
        .                 '<img src="' . $icon . '" alt="" />&nbsp;'
        .                 $toolName
        .                 '</a>' . "\n"
        ;
    }
    else
    {
        $toolLinkList[] = '<span ' . $style . '>'
        .                 '<img src="' . $icon . '" alt="" />&nbsp;'
        .                 $toolName
        .                 '</span>' . "\n"
        ;
    }
}


/*****************
 * DISPLAY SECTION
 ******************/

// CLAROLINE HEADER AND BANNER
include get_path('incRepositorySys') . '/claro_init_header.inc.php';

echo claro_html_tool_title( array('supraTitle'=> get_lang("Groups"),
                                  'mainTitle' => claro_get_current_group_data('name') . ' <img src="' . get_path('imgRepositoryWeb') . 'group.gif" alt="" />'));

if ( !empty($message) )
{
    echo claro_html_message_box($message);
}


if($is_allowedToSelfRegInGroup && !array_key_exists('registration',$_REQUEST))
{
    echo '<p>' . "\n"
    .    claro_html_cmd_link( $_SERVER['PHP_SELF'] . '?registration=1'
                            . claro_url_relay_context('&amp;')
                            , '<img src="' . get_path('imgRepositoryWeb') . 'enroll.gif"'
                            .     ' alt="' . get_lang("Add me to this group") . '" />'
    .                       get_lang("Add me to this group")
                            )
    .    '</p>'
    ;
}

echo '<table cellpadding="5" cellspacing="0" border="0">'  . "\n"
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
    echo claro_html_cmd_link( 'group_edit.php'
                            . claro_url_relay_context('?')
                            , '<img src="' . get_path('imgRepositoryWeb') . 'edit.gif"'
                            .     ' alt="' . get_lang("Edit this group") . '" />'
                            .    get_lang("Edit this group")
                            );
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

if( strlen(claro_get_current_group_data('description')) > 0)
{
    echo '<br /><br />' . "\n"
    .    claro_get_current_group_data('description')
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
        echo '<a href="../tracking/userLog.php?uInfo=' . $thisGroupMember['id']  . claro_url_relay_context('&amp;') . '" class="item">'
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

include get_path('incRepositorySys') . '/claro_init_footer.inc.php';


?>
