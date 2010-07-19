<?php // $Id$
/**
 * CLAROLINE
 *
 * This script edit userlist of a group and group propreties
 *
 * @version 1.9 $Revision$
 *
 * @copyright 2001-2009 Universite catholique de Louvain (UCL)
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

$tlabelReq = 'CLGRP';
require '../inc/claro_init_global.inc.php';
require_once get_path('incRepositorySys') . '/lib/form.lib.php';
require_once get_path('incRepositorySys') . '/lib/group.lib.inc.php';

if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);

$is_allowedToManage = claro_is_allowed_to_edit();

if ( ! $is_allowedToManage )
{
    claro_die(get_lang("Not allowed"));
}

$dialogBox = new DialogBox();
$nameTools = get_lang("Edit this group");

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_rel_user_course         = $tbl_mdb_names['rel_course_user'  ];
$tbl_user                    = $tbl_mdb_names['user'             ];

$tbl_bb_forum                = $tbl_cdb_names['bb_forums'];
$tbl_group_rel_team_user     = $tbl_cdb_names['group_rel_team_user'];
$tbl_group_team              = $tbl_cdb_names['group_team'];

$currentCourseId     = claro_get_current_course_id();
$_groupProperties = claro_get_current_group_properties_data();
$myStudentGroup      = claro_get_current_group_data();
$nbMaxGroupPerUser   = $_groupProperties ['nbGroupPerUser'];

if ( isset($_REQUEST['name']) ) $name = trim($_REQUEST['name']);
else                            $name = '';

if ( isset($_REQUEST['description']) ) $description = trim($_REQUEST['description']);
else                                   $description = '';
if ( isset($_REQUEST['maxMember']) && ctype_digit($_REQUEST['maxMember']) && (trim($_REQUEST['maxMember']) != '') ) $maxMember = (int) $_REQUEST['maxMember'];
else                                                                        $maxMember = NULL;

if ( isset($_REQUEST['tutor']) ) $tutor = (int) $_REQUEST['tutor'];
else                             $tutor = 0;

if ( isset($_REQUEST['ingroup']) ) $ingroup = $_REQUEST['ingroup'];
else                               $ingroup = array();


################### IF MODIFY #######################################

// Once modifications have been done, the user validates and arrives here
if ( isset($_REQUEST['modify']) && $is_allowedToManage )
{
    $sql = "UPDATE`" . $tbl_group_team . "`
            SET `name`        = '" . claro_sql_escape($name) . "',
                `description` = '" . claro_sql_escape($description) . "',
                `maxStudent`  = " . (is_null($maxMember) ? 'NULL' : "'" . (int) $maxMember . "'") .",
                `tutor`       = '" . (int) $tutor ."'
            WHERE `id`        = '" . (int) claro_get_current_group_id() . "'";


    // Update main group settings
    $updateStudentGroup = claro_sql_query($sql);

    // UPDATE FORUM NAME
    $sql = 'UPDATE `' . $tbl_bb_forum . '`
            SET `forum_name` ="' . claro_sql_escape($name).'"
            WHERE `forum_id` ="' . $myStudentGroup['forumId'] . '"';

    claro_sql_query($sql);

    // Count number of members
    $numberMembers = count($ingroup);

    // every letter introduced in field drives to 0
    settype($maxMember, 'integer');

    // Insert new list of members
    if ( $maxMember < $numberMembers AND $maxMember != '0' )
    {
        // Too much members compared to max members allowed
        $dialogBox->error( get_lang('Number proposed exceeds max. that you allowed (you can modify it below). Group composition has not been modified') );
    }
    else
    {
        // Delete all members of this group
        $sql = 'DELETE FROM `' . $tbl_group_rel_team_user . '` WHERE `team` = "' . (int)claro_get_current_group_id() . '"';

        $delGroupUsers = claro_sql_query($sql);
        $numberMembers--;

        for ($i = 0; $i <= $numberMembers; $i++)
        {
            $sql = "INSERT INTO `" . $tbl_group_rel_team_user . "`
                    SET user = " . (int) $ingroup[$i] . ",
                        team = " . (int) claro_get_current_group_id() ;

            $registerUserGroup = claro_sql_query($sql);
        }

        $dialogBox->success( get_lang("Group settings modified") );

    }    // else

    $gidReset = TRUE;
    $gidReq   = claro_get_current_group_id();

    include get_path('incRepositorySys') . '/claro_init_local.inc.php';

    $myStudentGroup = claro_get_current_group_data();

}    // end if $modify
// SELECT TUTORS

$tutorList = get_course_tutor_list($currentCourseId);

// AND student_group.id='claro_get_current_group_id()'    // This statement is DEACTIVATED

$tutor_list=array();
$tutor_list[get_lang("(none)")] = 0;
foreach ($tutorList as $myTutor)
{
    $tutor_list[htmlspecialchars($myTutor['name'] . ' ' . $myTutor['firstname'])]= $myTutor['userId'];
}

// Student registered to the course but inserted in no group
$limitNumOfGroups = (is_null($nbMaxGroupPerUser) || $nbMaxGroupPerUser == 0  ? "" :  " AND nbg < " . (int) $nbMaxGroupPerUser);

// Get the users not in group
$sql = "SELECT `u`.`user_id`        AS `user_id`,
               `u`.`nom`            AS `lastName`,
               `u`.`prenom`         AS `firstName`,
               `cu`.`role`          AS `role`,
               COUNT(`ug`.`id`)     AS `nbg`,
               COUNT(`ugbloc`.`id`) AS `BLOCK`
        
        FROM (`" . $tbl_user . "`                     AS u
           , `" . $tbl_rel_user_course . "`          AS cu )
        
        LEFT JOIN `" . $tbl_group_rel_team_user . "` AS ug
        ON `u`.`user_id`=`ug`.`user`
        
        LEFT JOIN `" . $tbl_group_rel_team_user . "` AS `ugbloc`
        ON  `u`.`user_id`=`ugbloc`.`user` AND `ugbloc`.`team` = " . (int) claro_get_current_group_id() . "
        
        WHERE `cu`.`code_cours` = '" . $currentCourseId . "'
        AND   `cu`.`user_id`    = `u`.`user_id`
        AND ( `cu`.`isCourseManager` = 0 )
        AND   `cu`.`tutor`      = 0
        AND ( `ug`.`team`       <> " . (int) claro_get_current_group_id() . " OR `ug`.`team` IS NULL )
        
        GROUP BY `u`.`user_id`
        HAVING `BLOCK` = 0
        " . $limitNumOfGroups . "
        ORDER BY
        #`nbg`, #disabled because different of  right box
        UPPER(`u`.`nom`), UPPER(`u`.`prenom`), `u`.`user_id`";

$result = Claroline::getDatabase()->query($sql);
$result->setFetchMode(Database_ResultSet::FETCH_ASSOC);

// Create html options lists
$userNotInGroupListHtml = '';
foreach ( $result as $member )
{
    $label = htmlspecialchars( ucwords( strtolower( $member['lastName'])) 
           . ' ' . ucwords(strtolower($member['firstName'] )) 
           . ($member['role']!=''?' (' . $member['role'] . ')':'') )
           . ( $nbMaxGroupPerUser > 1 ?' (' . $member['nbg'] . ')' : '' );
    
    $userNotInGroupListHtml .= '<option value="' 
                         . $member['user_id'] . '">' . $label 
                         . '</option>' . "\n";
}

$usersInGroupList = get_group_member_list();

$usersInGroupListHtml = '';
foreach ( $usersInGroupList as $key => $val )
{
    $usersInGroupListHtml .= '<option value="' 
                         . $key . '">' . $val 
                         . '</option>' . "\n";
}

$thisGroupMaxMember = ( is_null($myStudentGroup['maxMember']) ? '-' : $myStudentGroup['maxMember']);

$out = '';

$out .= claro_html_tool_title(array('supraTitle' => get_lang("Groups"), 'mainTitle' => $nameTools));

$out .= $dialogBox->render();

$out .= '<form class="msform" name="groupedit" method="post" '
.    'action="'
.    htmlspecialchars(
        $_SERVER['PHP_SELF'] . '?edit=yes&amp;gidReq=' . claro_get_current_group_id() 
     )
.    '">' . "\n"
.    claro_form_relay_context()
.    '<fieldset>' . "\n"
.    '<dl>'

    // Group name
.    '<dt><label for="name">' . get_lang("Group name") . '</label></dt>'
.    '<dd>' . "\n"
.    '<input type="text" name="name" id="name" size="40" value="' . htmlspecialchars($myStudentGroup['name']) . '" />' . "\n"
.    '<a href="group_space.php?gidReq=' . claro_get_current_group_id() . '">' . "\n"
.    '<img src="' . get_icon_url('group') . '" alt="" />' . "\n"
.    '&nbsp;' . get_lang("Area for this group") . '</a>' . "\n"
.    '</dd>' . "\n"

    // Group description
.    '<dt><label for="description">'.get_lang("Description").' '.get_lang("(optional)").'</label></dt>' . "\n"
.    '<dd>' . "\n"
.    '<textarea name="description" id="description" rows="4 "cols="70" >' . "\n"
.    htmlspecialchars($myStudentGroup['description']) . "\n"
.    '</textarea>' . "\n"
.    '</dd>' . "\n"

.    '<dt><label for="tutor">'.get_lang("Group Tutor").'</label></dt>'
.    '<dd>'
.    claro_html_form_select('tutor',$tutor_list,$myStudentGroup['tutorId'],array('id'=>'tutor')) . "\n"
.    '&nbsp;&nbsp;'
.    '<small>'
.    '<a href="../user/user.php?gidReset=true">'.get_lang("User list").'</a>'
.    '</small>'
.    '</dd>'

    // Maximum number of seats
.    '<dt><label for="maxMember">' . get_lang("Seats") . '</label></dt>'
.    '<dd><label for="maxMember">' . get_lang("Max.") . '</label> '
.    '<input type="text" name="maxMember" id="maxMember" size="2" 
        value="' .  htmlspecialchars($thisGroupMaxMember) . '" />' . "\n"
.    get_lang("seats (optional)")
.    '</dd>'

    // Group members
.    '<dt><label for="ingroup">' . get_lang("Group members") . '</label></dt>' . "\n"
.    '<dd>'
.    '<table>'
.    '<tr>'

.    '<td>'
.    '<label for="mslist1">'.get_lang("Users in group").'</label><br/>'
.    '<select multiple="multiple" name="ingroup[]" id="mslist1" size="10">'
.    $usersInGroupListHtml
.    '</select>'
.    '</td>'

.    '<td>'
.    '<a href="#" class="msadd"><img src="'.get_icon('go_right').'" /></a>'
.    '</input><br/><br/>'
.    '<a href="#" class="msremove"><img src="'.get_icon('go_left').'" /></a>'
.    '</input>'
.    '</td>'

.    '<td>'
.    '<label for="mslist2">'
.    ( get_conf('multiGroupAllowed') ? 
        (get_lang("Users not in this group")) : 
        (get_lang("Unassigned students"))
     )
.    '</label><br/>'
.    '<select multiple="multiple" name="nogroup[]" id="mslist2" size="10">'
.    $userNotInGroupListHtml
.    '</select>'
.    '</td>'

.    '</tr>'
.    '</table>'
.    '</dd>'
.    '<dt><input value="Ok" name="modify" type="submit" /></dt>'

.    '</dl>'
.    '</fieldset>'
.    '</form>';

$claroline->display->body->appendContent($out);

echo $claroline->display->render();


/**
 * Return a list of user and  groups of these users
 *
 * @param array     context
 * @return array    list of users
 */
function get_group_member_list($context = array())
{
    $currentCourseId = array_key_exists(CLARO_CONTEXT_COURSE, $context) ? $context['CLARO_CONTEXT_COURSE'] : claro_get_current_course_id();
    $currentGroupId  = array_key_exists(CLARO_CONTEXT_GROUP, $context) ? $context['CLARO_CONTEXT_GROUP'] : claro_get_current_group_id();
    
    $tblc = claro_sql_get_course_tbl();
    $tblm = claro_sql_get_main_tbl();
    
    $sql = "SELECT `ug`.`id`       AS id,
               `u`.`user_id`       AS user_id,
               `u`.`nom`           AS name,
               `u`.`prenom`        AS firstname,
               `u`.`email`         AS email,
               `u`.`officialEmail` AS officialEmail,
               `cu`.`role`         AS `role`
        FROM (`" . $tblm['user'] . "`           AS u
           , `" . $tblm['rel_course_user'] . "` AS cu
           , `" . $tblc['group_rel_team_user'] . "` AS ug)
        WHERE  `cu`.`code_cours` = '" . $currentCourseId . "'
          AND   `cu`.`user_id`   = `u`.`user_id`
          AND   `ug`.`team`      = " . (int) $currentGroupId . "
          AND   `ug`.`user`      = `u`.`user_id`
        ORDER BY UPPER(`u`.`nom`), UPPER(`u`.`prenom`), `u`.`user_id`";
    
    $result = Claroline::getDatabase()->query($sql);
    $result->setFetchMode(Database_ResultSet::FETCH_ASSOC);
    
    $usersInGroupList = array();
    foreach ( $result as $member )
    {
        $label = htmlspecialchars(ucwords(strtolower($member['name']))
        . ' ' . ucwords(strtolower($member['firstname']))
        . ($member['role']!=''?' (' . $member['role'] . ')':''));
        $usersInGroupList[$member['user_id']] = $label;
    }
    return $usersInGroupList;
}