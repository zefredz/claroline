<?php // $Id$
/**
 * CLAROLINE
 *
 * This script edit userlist of a group and group propreties
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

$tlabelReq = 'CLGRP___';
require '../inc/claro_init_global.inc.php';
require_once $includePath . '/lib/form.lib.php';

if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);

$is_allowedToManage = $is_courseAdmin;

if ( ! $is_allowedToManage )
{
    claro_die(get_lang("Not allowed"));
}

$nameTools = get_lang("Edit this group");

$htmlHeadXtra[]='
<script type="text/javascript" language="JavaScript">
<!-- Begin javascript menu swapper

function move(fbox, tbox)
{
 var arrFbox = new Array();
 var arrTbox = new Array();
 var arrLookup = new Array();
 var i;

 for (i = 0; i < tbox.options.length; i++)
 {
  arrLookup[tbox.options[i].text] = tbox.options[i].value;
  arrTbox[i] = tbox.options[i].text;
 }

 var fLength = 0;
 var tLength = arrTbox.length;

 for(i = 0; i < fbox.options.length; i++)
 {
  arrLookup[fbox.options[i].text] = fbox.options[i].value;
  if (fbox.options[i].selected && fbox.options[i].value != "")
  {
   arrTbox[tLength] = fbox.options[i].text;
   tLength++;
  }
  else
  {
    arrFbox[fLength] = fbox.options[i].text;
    fLength++;
  }
 }

 arrFbox.sort();
 arrTbox.sort();
 fbox.length = 0;
 tbox.length = 0;
 var c;

 for(c = 0; c < arrFbox.length; c++)
 {
  var no = new Option();
  no.value = arrLookup[arrFbox[c]];
  no.text = arrFbox[c];
  fbox[c] = no;
 }

 for(c = 0; c < arrTbox.length; c++)
 {
  var no = new Option();
  no.value = arrLookup[arrTbox[c]];
  no.text = arrTbox[c];
  tbox[c] = no;
 }
}
//  End -->
</script>

<script type="text/javascript" language="JavaScript">

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
</script>
';


$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_rel_user_course         = $tbl_mdb_names['rel_course_user'  ];
$tbl_user                    = $tbl_mdb_names['user'             ];

$tbl_bb_forum                = $tbl_cdb_names['bb_forums'];
$tbl_course_group_property   = $tbl_cdb_names['group_property'];
$tbl_group_rel_team_user     = $tbl_cdb_names['group_rel_team_user'];
$tbl_group_team              = $tbl_cdb_names['group_team'];

$currentCourseId     = $_course['sysCode'];
$myStudentGroup      = $_group;
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
            SET `name`        = '" . addslashes($name) . "',
                `description` = '" . addslashes($description) . "',
                `maxStudent`  = " . (is_null($maxMember) ? 'NULL' : "'" . (int) $maxMember . "'") .",
                `tutor`       = '" . (int) $tutor ."'
            WHERE `id`        = '" . (int) $_gid . "'";


    // Update main group settings
    $updateStudentGroup = claro_sql_query($sql);

    // UPDATE FORUM NAME
    $sql = 'UPDATE `' . $tbl_bb_forum . '`
            SET `forum_name` ="' . addslashes($name).'"
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
        $messageGroupEdited = get_block('GroupTooMuchMembers');
    }
    else
    {
        // Delete all members of this group
        $sql = 'DELETE FROM `' . $tbl_group_rel_team_user . '` WHERE `team` = "' . (int)$_gid . '"';

        $delGroupUsers = claro_sql_query($sql);
        $numberMembers--;

        for ($i = 0; $i <= $numberMembers; $i++)
        {
            $sql = "INSERT INTO `" . $tbl_group_rel_team_user . "`
                    SET user = " . (int) $ingroup[$i] . ",
                        team = " . (int) $_gid ;

            $registerUserGroup = claro_sql_query($sql);
        }

        $messageGroupEdited = get_lang("Group settings modified");

    }    // else

    $gidReset = TRUE;
    $gidReq   = $_gid;

    include $includePath . '/claro_init_local.inc.php';

    $myStudentGroup = $_group;

}    // end if $modify
// SELECT TUTORS

$sql = "SELECT `user`.`user_id` AS `user_id` ,
                   `user`.`nom`     AS `nom`,
                   `user`.`prenom`  AS `prenom`
            FROM `" . $tbl_user . "` AS `user`,
                 `" . $tbl_rel_user_course . "` AS `cours_user`
            WHERE `cours_user`.`user_id`    = `user`.`user_id`
            AND   `cours_user`.`tutor`      = 1
            AND   `cours_user`.`code_cours` = '" . $currentCourseId . "'";

$resultTutor = claro_sql_query_fetch_all($sql);

// AND student_group.id='$_gid'    // This statement is DEACTIVATED

$tutor_list=array();
foreach ($resultTutor as $myTutor)
{
    $tutor_list[$myTutor['user_id']]= htmlspecialchars($myTutor['nom'] . ' ' . $myTutor['prenom']);
}
$tutor_list[0] = get_lang("(none)");

$sql = "SELECT `ug`.`id`     AS id,
               `u`.`user_id` AS user_id,
               `u`.`nom`     AS name,
               `u`.`prenom`  AS firstname,
               `u`.`email`   AS email
        FROM `" . $tbl_user . "`                AS u
           , `" . $tbl_group_rel_team_user . "` AS ug
        WHERE `ug`.`team` = " . (int) $_gid . "
        AND   `ug`.`user` = `u`.`user_id`
        ORDER BY UPPER(`u`.`nom`), UPPER(`u`.`prenom`)";

$resultMember = claro_sql_query_fetch_all($sql);
$usersInGroupList=array();
foreach ($resultMember as $thisMember )
{
    $usersInGroupList[$thisMember['user_id']]= htmlspecialchars(ucwords(strtolower($thisMember['name'])) . ' ' . ucwords(strtolower($thisMember['firstname'])));
}

// Student registered to the course but inserted in no group

$limitNumOfGroups = (is_null($nbMaxGroupPerUser) ? "" :  " AND nbg < " . (int) $nbMaxGroupPerUser);

// Initialise userNotInGroupList to empty array
$userNotInGroupList = array();

$sql = "SELECT `u`.`user_id`        AS `user_id`,
               `u`.`nom`            AS `lastName`,
               `u`.`prenom`         AS `firstName`,
               COUNT(`ug`.`id`)     AS `nbg`,
               COUNT(`ugbloc`.`id`) AS `BLOCK`

        FROM `" . $tbl_user . "`                     AS u
           , `" . $tbl_rel_user_course . "`          AS cu

        LEFT JOIN `" . $tbl_group_rel_team_user . "` AS ug
        ON `u`.`user_id`=`ug`.`user`

        LEFT JOIN `" . $tbl_group_rel_team_user . "` AS `ugbloc`
        ON  `u`.`user_id`=`ugbloc`.`user` AND `ugbloc`.`team` = " . (int) $_gid . "

        WHERE `cu`.`code_cours` = '" . $currentCourseId . "'
        AND   `cu`.`user_id`    = `u`.`user_id`
        AND ( `cu`.`statut`     = 5            OR `cu`.`statut` IS NULL)
        AND   `cu`.`tutor`      = 0
        AND ( `ug`.`team`       <> " . (int) $_gid . " OR `ug`.`team` IS NULL )

        GROUP BY `u`.`user_id`
        HAVING `BLOCK` = 0
        " . $limitNumOfGroups . "
        ORDER BY
        #`nbg`, #disabled because different of  right box
        UPPER(`u`.`nom`), UPPER(`u`.`prenom`)";

$result = claro_sql_query_fetch_all($sql);

foreach ($result AS $myNotMember )
{
    $userNotInGroupList[$myNotMember['user_id']] =
    htmlspecialchars(ucwords(strtolower($myNotMember['lastName'])) . ' ' . ucwords(strtolower($myNotMember['firstName'])))
    .    ( $nbMaxGroupPerUser > 1 ?' (' . $myNotMember['nbg'] . ')' : '' )
    ;
}
$thisGroupMaxMember = ( is_null($myStudentGroup['maxMember']) ? '-' : $myStudentGroup['maxMember']);




$interbredcrump[]= array ('url' => 'group.php', 'name' => get_lang("Groups"));
$interbredcrump[]= array ('url' => 'group_space.php?gidReq=' . $_gid, 'name' => $myStudentGroup['name'] );

include($includePath . '/claro_init_header.inc.php');

echo claro_html::tool_title(array('supraTitle' => get_lang("Groups"),
'mainTitle' => $nameTools));

if ( isset($messageGroupEdited) ) echo claro_html::message_box($messageGroupEdited);


echo '<form name="groupedit" method="POST" action="' . $_SERVER['PHP_SELF'] . '?edit=yes&amp;gidReq=' . $_gid . '">' . "\n"
.    '<table border="0" cellspacing="3" cellpadding="5">' . "\n"
.    '<tr valign="top">' . "\n"
.    '<td align="right">' . "\n"
.    '<label for="name" >' . get_lang("Group name") . '</label> : ' . "\n"
.    '</td>' . "\n"
.    '<td colspan="2">' . "\n"
.    '<input type="text" name="name" id="name" size="40" value="' . htmlspecialchars($myStudentGroup['name']) . '">' . "\n"
.    '</td>' . "\n"
.    '<td>' . "\n"
.    '<a href="group_space.php?gidReq=' . $_gid . '">' . "\n"
.    '<img src="' . $imgRepositoryWeb . 'group.gif" />' . "\n"
.    '&nbsp;' . get_lang("Area for this group") . '</a>' . "\n"
.    '</td>' . "\n"
.    '</tr>' . "\n"
.    '<tr valign="top">' . "\n"
.    '<td align="right">' . "\n"
.    '<label for="description">' . "\n"
.    get_lang("Description") . ' ' . get_lang("(optional)") . "\n"
.    '</label> :' . "\n"
.    '<td colspan="3">' . "\n"
.    '<textarea name="description" id="description" rows="4 "cols="70" wrap="virtual">' . "\n"
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
.    '<small><a href="../user/user.php">' . get_lang("User list") . '</a></small>'
.    '<td>'
.    '<label for="maxMember">' . get_lang("Max.") . '</label> '

.   '<input type="text" name="maxMember" id="maxMember" size="2" value="' .  htmlspecialchars($thisGroupMaxMember) . '" / >' . "\n"

.    get_lang("seats (optional)")
.    '</td>'
.    '</tr>'
################### STUDENTS IN AND OUT GROUPS #######################
.    '<tr valign="top">'
.    '<td align="right"><label for="inGroup">' . get_lang("Group members") . '</label> : </td>' . "\n"
.    '<td>'
.    claro_html_form_select('ingroup[]',$usersInGroupList,'',array('id'=>'ingroup', 'size'=>'8', 'multiple'=>'multiple'))
.    '<br />' . "\n"
.    '<br />' . "\n"
.    '<input type=submit value="' . get_lang("Ok") . '" name="modify" onClick="selectAll(this.form.elements[\'ingroup\'],true)" />' . "\n"
.    '</td>' . "\n"
.    '<td>' . "\n"
.    '<!-- ' . "\n"
.    'WATCH OUT ! form elements are called by numbers "form.element[3]"...' . "\n"
.    'because select name contains "[]" causing a javascript element name problem' . "\n"
.    ' -->' . "\n"
.    '<br />' . "\n"
.    '<br />' . "\n"
.    '<input type="button" onClick="move(this.form.elements[\'ingroup\'],this.form.elements[\'nogroup\'])" value="   >>   " />' . "\n"
.    '<br />' . "\n"
.    '<input type="button" onClick="move(this.form.elements[\'nogroup\'],this.form.elements[\'ingroup\'])" value="   <<   " / >' . "\n"
.    '</td>' . "\n"
.    '<td>' . "\n"
.    claro_html_form_select('nogroup[]',$userNotInGroupList,'',array('id'=>'nogroup', 'size'=>'8', 'multiple'=>'multiple')) . "\n"
.    '<br />' . "\n"
;

if ( get_conf('multiGroupAllowed') ) echo get_lang("Users not in this group");
else                                 echo get_lang("Unassigned students");
?>
</td>
</tr>
<tr valign="top">
<td colspan="4">&nbsp;</td>
</tr>
</table>
</form>

<?php
include $includePath . '/claro_init_footer.inc.php';
?>