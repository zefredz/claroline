<?php // $Id$
/**
 * CLAROLINE
 *
 * This script edit userlist of a group and group propreties
 *
 * @version 1.9 $Revision$
 * @copyright 2001-2011 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see http://www.claroline.net/wiki/index.php/CLGRP
 * @package CLGRP
 * @author Claro Team <cvs@claroline.net>
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

$htmlHeadXtra[]='
<script type="text/javascript" language="JavaScript">
<!-- Begin javascript menu swapper
function move( inBox, outBox )
{
    var arrInBox = new Array();
    var arrOutBox = new Array();

    for ( var i=0; i<outBox.options.length; i++ )
    {
        arrOutBox[i] = outBox.options[i];
    }

    var outLength = arrOutBox.length;
    var inLength = 0;

    for ( var i=0; i<inBox.options.length; i++ )
    {
        var opt = inBox.options[i];
        if ( opt.selected )
        {
            arrOutBox[outLength] = opt;
            outLength++;
        }
        else
        {
            arrInBox[inLength] = opt;
            inLength++;
        }
    }

    inBox.length = 0;
    outBox.length = 0;

    for ( var i = 0; i < arrOutBox.length; i++ )
    {
        outBox.options[i] = arrOutBox[i];
    }

    for ( var i = 0; i < arrInBox.length; i++ )
    {
        inBox.options[i] = arrInBox[i];
    }
}
//  End -->
</script>

<script type="text/javascript" language="JavaScript">
<!-- 
function selectAll(cbList,bSelect) {
  for (var i=0; i<cbList.length; i++)
    cbList[i].selected = cbList[i].checked = bSelect
}

function reverseAll(cbList) {
  for (var i=0; i<cbList.length; i++) {
    cbList[i].checked = !(cbList[i].checked)
    cbList[i].selected = !(cbList[i].selected)
  }
}
 -->
</script>
';


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

$usersInGroupList = get_group_member_list();

// Student registered to the course but inserted in no group
$limitNumOfGroups = (is_null($nbMaxGroupPerUser) || $nbMaxGroupPerUser == 0  ? "" :  " AND nbg < " . (int) $nbMaxGroupPerUser);

// Initialise userNotInGroupList to empty array
$userNotInGroupList = array();

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

$result = claro_sql_query_fetch_all($sql);


foreach ($result AS $myNotMember )
{
    $label = htmlspecialchars( ucwords( strtolower( $myNotMember['lastName'])) . ' ' . ucwords(strtolower($myNotMember['firstName'] )) . ($myNotMember['role']!=''?' (' . $myNotMember['role'] . ')':'') )
    .    ( $nbMaxGroupPerUser > 1 ?' (' . $myNotMember['nbg'] . ')' : '' )
    ;
    $userNotInGroupList[$myNotMember['user_id']] = $label;
}
$thisGroupMaxMember = ( is_null($myStudentGroup['maxMember']) ? '-' : $myStudentGroup['maxMember']);

$out = '';

$out .= claro_html_tool_title(array('supraTitle' => get_lang("Groups"), 'mainTitle' => $nameTools));

$out .= $dialogBox->render();

$out .= '<form name="groupedit" method="post" action="'
.    htmlspecialchars(
        $_SERVER['PHP_SELF'] . '?edit=yes&amp;gidReq=' . claro_get_current_group_id() )
.    '">' . "\n"
.    claro_form_relay_context()
.    '<table border="0" cellspacing="3" cellpadding="5">' . "\n"
.    '<tr valign="top">' . "\n"
.    '<td align="right">' . "\n"
.    '<label for="name" >' . get_lang("Group name") . '</label> : ' . "\n"
.    '</td>' . "\n"
.    '<td colspan="2">' . "\n"
.    '<input type="text" name="name" id="name" size="40" value="' . htmlspecialchars($myStudentGroup['name']) . '" />' . "\n"
.    '</td>' . "\n"
.    '<td>' . "\n"
.    '<a href="group_space.php?gidReq=' . claro_get_current_group_id() . '">' . "\n"
.    '<img src="' . get_icon_url('group') . '" alt="" />' . "\n"
.    '&nbsp;' . get_lang("Area for this group") . '</a>' . "\n"
.    '</td>' . "\n"
.    '</tr>' . "\n"
.    '<tr valign="top">' . "\n"
.    '<td align="right">' . "\n"
.    '<label for="description">' . "\n"
.    get_lang("Description") . ' ' . get_lang("(optional)") . "\n"
.    '</label> :' . "\n"
.    '</td>' . "\n"
.    '<td colspan="3">' . "\n"
.    '<textarea name="description" id="description" rows="4 "cols="70" >' . "\n"
.    htmlspecialchars($myStudentGroup['description']) . "\n"
.    '</textarea>' . "\n"
.    '</td>' . "\n"
.    '</tr>' . "\n"
.    '' . "\n"
.    '<tr valign="top">' . "\n"
.    '<td align="right">' . "\n"
.    '<label for="tutor">' . "\n"
.    get_lang("Group Tutor") . '</label> : ' . "\n"
.    '</td>' . "\n"
.    '<td colspan="2">' . "\n"
.    claro_html_form_select('tutor',$tutor_list,$myStudentGroup['tutorId'],array('id'=>'tutor')) . "\n"
.    '&nbsp;&nbsp;'
.    '<small>'
.    '<a href="../user/user.php?gidReset=true">'
.    get_lang("User list")
.    '</a>'
.    '</small>'
.    '</td>'
.    '<td>'
.    '<label for="maxMember">' . get_lang("Max.") . '</label> '

.   '<input type="text" name="maxMember" id="maxMember" size="2" value="' .  htmlspecialchars($thisGroupMaxMember) . '" />' . "\n"

.    get_lang("seats (optional)")
.    '</td>'
.    '</tr>'
################### STUDENTS IN AND OUT GROUPS #######################
.    '<tr valign="top">'
.    '<td align="right">'
.    '<label for="ingroup">' . get_lang("Group members") . '</label>'
.    ' : '
.    '</td>' . "\n"
.    '<td>'
.    claro_html_form_select('ingroup[]',$usersInGroupList,'',array('id'=>'ingroup', 'size'=>'8', 'multiple'=>'multiple'),true)
.    '<br />' . "\n"
.    '<br />' . "\n"
.    '<input type="submit" value="' . get_lang("Ok") . '" name="modify" onclick="selectAll(this.form.elements[\'ingroup\'],true)" />' . "\n"
.    '</td>' . "\n"
.    '<td>' . "\n"
.    '<!-- ' . "\n"
.    'WATCH OUT ! form elements are called by numbers "form.element[3]"...' . "\n"
.    'because select name contains "[]" causing a javascript element name problem' . "\n"
.    ' -->' . "\n"
.    '<br />' . "\n"
.    '<br />' . "\n"
.    '<input type="button" onclick="move(this.form.elements[\'ingroup\'],this.form.elements[\'nogroup\'])" value="   >>   " />' . "\n"
.    '<br />' . "\n"
.    '<input type="button" onclick="move(this.form.elements[\'nogroup\'],this.form.elements[\'ingroup\'])" value="   <<   " />' . "\n"
.    '</td>' . "\n"
.    '<td>' . "\n"
.    claro_html_form_select('nogroup[]',$userNotInGroupList,'',array('id'=>'nogroup', 'size'=>'8', 'multiple'=>'multiple'), true) . "\n"
.    '<br />' . "\n"
;

if ( get_conf('multiGroupAllowed') ) $out .= get_lang("Users not in this group");
else                                 $out .= get_lang("Unassigned students");

$out .= '</td>'
.    '</tr>'
.    '<tr valign="top">'
.    '<td colspan="4">&nbsp;</td>'
.    '</tr>'
.    '</table>'
.    '</form>'
;

$claroline->display->body->appendContent($out);

echo $claroline->display->render();


/**
 * return a list of user and  groups of these users
 *
 * @param unknown_type $context
 * @return unknown
 */
function get_group_member_list($context=array())
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

    $resultMember = claro_sql_query_fetch_all($sql);
    $usersInGroupList=array();
    foreach ($resultMember as $thisMember )
    {
        $label = htmlspecialchars(ucwords(strtolower($thisMember['name']))
        . ' ' . ucwords(strtolower($thisMember['firstname']))
        . ($thisMember['role']!=''?' (' . $thisMember['role'] . ')':''));
        $usersInGroupList[$thisMember['user_id']] = $label;
    }
    return $usersInGroupList;
}
