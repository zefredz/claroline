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
$tlabelReq = 'CLGRP___';

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
    header('Location:group.php');
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
        $sql = 'INSERT INTO `' . $tbl_group_rel_team_user . '`
                SET `user` = "' . (int) $_uid . '",
                    `team` = "' . (int) $_gid . '"';

        if (claro_sql_query($sql))
        {
            // REFRESH THE SCRIPT TO COMPUTE NEW PERMISSIONS ON THE BASSIS OF THIS CHANGE
            header('Location:' . $_SERVER['PHP_SELF'] . '?gidReset=1&gidReq=' . $_gid . '&regDone=1');
            exit();

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

foreach($toolList as $thisTool)
{

    if ( ! empty($thisTool['label']))   // standart claroline tool
    {
        $toolName = get_lang( $toolNameList[ $thisTool['label'] ] );
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

    $style = '';

    // patchy
    if ( $is_platformAdmin || $is_courseAdmin )
    {
        switch (trim($thisTool['label'],'_'))
        {
            case 'CLDOC' :
            {
                if(!$_groupProperties['tools']['document'])
                {
                    $style = 'invisible ';
                }
            } break;

            case 'CLFRM' :
            {
                if(!$_groupProperties['tools']['forum'])
                {
                    $style = 'invisible ';
                }

            } break;

            case 'CLWIKI' :
            {
                if(!$_groupProperties['tools']['wiki'])
                {
                    $style = 'invisible ';
                }
            } break;

            case 'CLCHT' :
            {
                if(!$_groupProperties['tools']['chat'])
                {
                    $style = 'invisible ';
                }
            }break;
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


if($is_allowedToSelfRegInGroup)
{
    echo '<p>' . "\n"
    .    '<a href="' . $_SERVER['PHP_SELF'] . '?registration=1" class="claroCmd">'
    .    '<img src="' . $imgRepositoryWeb . 'enroll.gif" alt="' . get_lang("Add me to this group") . '" />'
    .    get_lang("Add me to this group")
    .    '</a>' . "\n"
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


/**
 * This dirty function is a blackbox to provide normalised output of tool list for a group
 * like  get_course_tool_list($course_id=NULL) in course_home.
 *
 * It's dirty because data structure is dirty.
 * Tool_list (with clarolabel and tid come from tool tables and  group properties and localinit)
 * @param $course_id 
 * @param boolean $active, if set to true, only activated tools of the platform must be returned 
 * @author Christophe Gesché <moosh@claroline.net>
 * @return array
 */


function get_group_tool_list($course_id=NULL,$active = true)
{
    global $_groupProperties, $forumId, $is_courseAdmin, $is_platformAdmin;

    $isAllowedToEdit = $is_courseAdmin || $is_platformAdmin;

    $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_course_tool = $tbl_cdb_names['tool'];

    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_tool  = $tbl_mdb_names['tool'];

    $aivailable_tool_in_group = array('CLFRM','CLCHT','CLDOC','CLWIKI');

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

    $group_tool_list = array();

    foreach($tool_list as $tool)
    {
        if (in_array(trim($tool['label'],'_'),$aivailable_tool_in_group))
        {
            switch (trim($tool['label'],'_'))
            {
                case 'CLDOC' :
                {
                    if($_groupProperties['tools']['document'] || $isAllowedToEdit)
                    {
                        $group_tool_list[] = $tool;
                    }
                } break;

                case 'CLFRM' :
                {
                    if($_groupProperties['tools']['forum'] || $isAllowedToEdit)
                    {
                        $tool['url'] = 'phpbb/viewforum.php?forum=' . $forumId ;
                        $group_tool_list[] = $tool;
                    }

                } break;

                case 'CLWIKI' :
                {
                    if($_groupProperties['tools']['wiki'] || $isAllowedToEdit)
                    {
                        $group_tool_list[] = $tool;
                    }
                } break;

                case 'CLCHT' :
                {
                    if($_groupProperties['tools']['chat'] || $isAllowedToEdit)
                    {
                        $group_tool_list[] = $tool;
                    }
                }break;

            }
        }
    }

    if ($active)
    {
        //substrack the deactivated tool list, if needed
        
        $deactivated_tools = claro_get_deactivated_tool_list();
        
        foreach ($group_tool_list as $key=>$tool)
        {
            if (in_array($tool['label'], $deactivated_tools))
            {
                unset($group_tool_list[$key]);
            }
        }
    }

    return $group_tool_list;
}

?>