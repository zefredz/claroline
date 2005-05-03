<?php // $Id$
/**
 * CLAROLINE 
 *
 * This tool list user member of the course.
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLUSR
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package CLUSR
 *
 */

/*=====================================================================
   Initialisation
  =====================================================================*/

$tlabelReq = 'CLUSR___';

require '../inc/claro_init_global.inc.php';

if ( ! isset($_cid) ) claro_disp_select_course();

if ( ! $is_courseAllowed ) claro_disp_auth_form();

claro_set_display_mode_available(true);

/*----------------------------------------------------------------------
   Include Library
  ----------------------------------------------------------------------*/

include($includePath.'/lib/admin.lib.inc.php');
include($includePath."/lib/pager.lib.php");
include($includePath.'/lib/events.lib.inc.php');
@include($includePath.'/lib/debug.lib.inc.php');

/*----------------------------------------------------------------------
  Stats
  ----------------------------------------------------------------------*/

event_access_tool($_tid, $_courseTool['label']);

/*----------------------------------------------------------------------
   JavaScript - Delete Confirmation
  ----------------------------------------------------------------------*/

$htmlHeadXtra[] =
'
<script type="text/javascript" language="JavaScript" >
function confirmation (name)
{
    if (confirm(" '.clean_str_for_javascript($langAreYouSureToDelete).' "+ name + " ?"))
        {return true;}
    else
        {return false;}
}
</script>
';

/*----------------------------------------------------------------------
   Variables
  ----------------------------------------------------------------------*/

$userPerPage = isset($nbUsersPerPage)?$nbUsersPerPage:50;

$is_allowedToEdit = claro_is_allowed_to_edit();

$can_add_user     = (bool) (   $is_courseAdmin 
                     && isset($is_coursemanager_allowed_to_add_user)
                     && $is_coursemanager_allowed_to_add_user)
                     || $is_platformAdmin;

$currentCourse = $currentCourseID  = $_course['sysCode'];

/*----------------------------------------------------------------------
  DB tables definition
  ----------------------------------------------------------------------*/

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();

$tbl_rel_course_user = $tbl_mdb_names['rel_course_user'  ];
$tbl_users           = $tbl_mdb_names['user'             ];
$tbl_courses_users   = $tbl_rel_course_user;

$tbl_rel_users_groups= $tbl_cdb_names['group_rel_team_user'    ];
$tbl_groups          = $tbl_cdb_names['group_team'             ];

/*=====================================================================
  Main section
  =====================================================================*/

if ( $is_allowedToEdit )
{
    $disp_tool_link = TRUE;

    // Unregister user from course
    // (notice : it does not delete user from claroline main DB)

    if ( isset($_REQUEST['unregister']) && $_REQUEST['unregister'] )
    {
        // delete user from course user list
        if ( remove_user_from_course($user_id, $_cid) )
        {
           $dialogBox = $langUserUnsubscribedFromCourse;
        }
        else
        {
           $dialogBox = $langUserNotUnsubscribedFromCourse;
        }
   }
}    // end if allowed to edit

/*----------------------------------------------------------------------
   Get total user
  ----------------------------------------------------------------------*/

$sqlNbUser = 'SELECT count(user.user_id) `nb_users`
              FROM `'.$tbl_rel_course_user.'` `cours_user`,
                   `'.$tbl_users.'` `user`
              WHERE `cours_user`.`code_cours` = "'.$currentCourseID.'"
              AND cours_user.user_id = `user`.user_id';

$userTotalNb = claro_sql_query_fetch_all($sqlNbUser);

$userTotalNb = $userTotalNb[0]['nb_users'];

/*----------------------------------------------------------------------
   Get User List
  ----------------------------------------------------------------------*/

$sqlGetUsers ='SELECT `user`.`user_id`, `user`.`nom`, `user`.`prenom`, 
                      `user`.`email`, `cours_user`.`statut`, 
                      `cours_user`.`tutor`, `cours_user`.`role`
               FROM `'.$tbl_users.'` `user`, `'.$tbl_rel_course_user.'` `cours_user`
               WHERE `user`.`user_id`=`cours_user`.`user_id`
               AND `cours_user`.`code_cours`="'.$currentCourseID.'"
               ORDER BY `cours_user`.`statut` ASC, `cours_user`.`tutor` DESC,
                        UPPER(`user`.`nom`), UPPER(`user`.`prenom`) ';

if ( !isset($_REQUEST['offset']) )
{
    $offset = "0";
}
else
{
    $offset = $_REQUEST['offset'];
}

$myPager = new claro_sql_pager($sqlGetUsers, $offset, $userPerPage);
$userList = $myPager->get_result_list();

/*----------------------------------------------------------------------
  Get groups
  ----------------------------------------------------------------------*/

foreach ( $userList as $thisUser )
{
    $users[$thisUser['user_id']]    = $thisUser;
    $usersId[]    = $thisUser['user_id'];
}

$sqlGroupOfUsers = "SELECT `ug`.`user` uid, `ug`.`team` team, 
                    `sg`.`name` nameTeam
                    FROM `".$tbl_rel_users_groups."` `ug`
                    LEFT JOIN `".$tbl_groups."` `sg`
                    ON `ug`.`team` = `sg`.`id`
                    WHERE `ug`.`user` IN (".implode(",",$usersId).")";

$resultUserGroup = claro_sql_query($sqlGroupOfUsers);

$usersGroup = array();

while ($thisAffiliation = mysql_fetch_array($resultUserGroup,MYSQL_ASSOC))
{
    $usersGroup[$thisAffiliation['uid']][$thisAffiliation['team']]['nameTeam'] = $thisAffiliation['nameTeam'];
}

/*=====================================================================
  Display section
  =====================================================================*/

$nameTools = $langUsers;

// Display header

include($includePath.'/claro_init_header.inc.php');

claro_disp_tool_title($nameTools.' ('.$langUserNumber.' : '.$userTotalNb.')',
            $is_allowedToEdit ? 'help_user.php' : FALSE);

// Display Forms or dialog box(if needed)

if ( !empty($dialogBox) )
{
    claro_disp_message_box($dialogBox);
}

// Display tool links

if ( $disp_tool_link )
{
    echo "<p>";
    if ($can_add_user)
    { 
       //add a user link
    ?>
    <a class="claroCmd" href="user_add.php"><img src="<?php echo $imgRepositoryWeb; ?>user.gif"><?php echo $langAddAU; ?></a> |
    <?php
       //add CSV file of user link
    ?>
    <a class="claroCmd" href="AddCSVusers.php?AddType=userTool"><img src="<?php echo $imgRepositoryWeb; ?>importlist.gif"> <?php echo $langAddListUser; ?></a> |
    <?php 
       //add a class link
    ?>
    <a class="claroCmd" href="class_add.php"><img src="<?php echo $imgRepositoryWeb; ?>class.gif"> <?php echo $langAddClass; ?></a> |
    <?php
    
    }
    ?>
    <a class="claroCmd" href="../group/group.php"><img src="<?php echo $imgRepositoryWeb; ?>group.gif"><?php echo $langGroupUserManagement; ?></a>
    </p>
<?php
}

/*----------------------------------------------------------------------
   Display pager
  ----------------------------------------------------------------------*/

$myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

/*----------------------------------------------------------------------
   Display table header
  ----------------------------------------------------------------------*/

echo '<table class="claroTable emphaseLine" '
   . 'width="100%" cellpadding="2" cellspacing="1" '
   . 'border="0" summary="'.$langListCourseUsers.'">'."\n"
   . '<colgroup span="3" align="left"></colgroup>'."\n"
   ;

    if($is_allowedToEdit)
    {
        echo '<colgroup span="2"></colgroup>'."\n"
           . '<colgroup span="2" width="0" ></colgroup>'."\n"
           ;
    }

    echo '<thead>'."\n"
       . '<tr class="headerX" align="center" valign="top">'."\n"
       . '<th scope="col" id="name">'.$langUserName.'</th>'."\n"
       . '<th scope="col" id="role">'.$langRole.'</th>'."\n"
       . '<th scope="col" id="team">'.$langGroup.'</th>'."\n"
       ;

    if($is_allowedToEdit) // EDIT COMMANDS
    {
        echo '<th scope="col" id="tut"  >'.$langGroupTutor.'</th>'."\n"
           . '<th scope="col" id="CM"   >'.$langCourseManager.'</th>'."\n"
           . '<th scope="col" id="edit" >'.$langEdit.'</th>'."\n"
           . '<th scope="col" id="del"  >'.$langUnreg.'</th>'."\n"
           ;
    }

echo '</tr>'."\n"
   . '</thead>'."\n"
   . '<tbody>'."\n"
   ;

/*----------------------------------------------------------------------
   Display users
  ----------------------------------------------------------------------*/

$i = $offset;
$previousUser = -1;

reset($userList);

foreach ( $userList as $thisUser )
{
    // User name column
    $i++;
    echo '<tr align="center" valign="top">'."\n"
       . '<td id="u'.$i.'" headers="name" align="left">'
       . '<img src="'.$imgRepositoryWeb.'user.gif">'."\n"
       . '<small>' . $i . '</small>'."\n"
       . '&nbsp;'
       . '<a href="userInfo.php?uInfo='.$thisUser['user_id'].'">'
       . ucfirst(strtolower($thisUser['prenom'])) . ' ' . ucfirst(strtolower($thisUser['nom']))
       . '</a>'
       . '</td>'."\n"
       // User role column
       . '<td headers="role u'.$i.'" align="left">'.$thisUser['role'].'</td>'."\n"
       ;
    
    // User group column
    if ( !isset ($usersGroup[$thisUser['user_id']]) )    // NULL and not '0' because team can be inexistent
    {
        echo '<td headers="team" > - </td>'."\n";
    }
    else
    {
        $userGroups = $usersGroup[$thisUser['user_id']];
        echo '<td headers="team u'.$i.'">'."\n";
        reset($userGroups);
        while (list($thisGroupsNo,$thisGroupsName)=each($userGroups))
        {
            echo '<div>'
               . $thisGroupsName["nameTeam"]
               . '<small>('.$thisGroupsNo.')</small>'
               . '</div>';
        }
        echo '</td>'."\n";
    }

    if ($previousUser == $thisUser['user_id'])
    {
        echo '<td headers="team u'.$i.'" >&nbsp;</td>'."\n";
    }
    elseif ( $is_allowedToEdit )
    {
        // Tutor column
        if($thisUser['tutor'] == '0')
        {
            echo '<td headers="tut u'.$i.'"> - </td>'."\n";
        }
        else
        {
            echo '<td headers="tut u'.$i.'">'.$langGroupTutor.'</td>'."\n";
        }

        // course manager column
        if($thisUser['statut'] == '1')
        {
            echo '<td headers="CM u'.$i.'">'.$langCourseManager.'</td>'."\n";
        }
        else
        {
            echo '<td headers="CM u'.$i.'"> - </td>'."\n";
        }

        // Edit user column
        echo '<td headers="edit u'.$i.'">'
           . '<a href="userInfo.php?editMainUserInfo='.$thisUser['user_id'].'">'
           . '<img border="0" alt="'.$langEdit.'" src="'.$imgRepositoryWeb.'edit.gif">'
           . '</a>'
           . '</td>'."\n"
        // Unregister user column
           . '<td headers="del u'.$i.'" >';

        if ($thisUser['user_id'] != $_uid)
        {
            echo '<a href="'.$_SERVER['PHP_SELF'].'?unregister=yes&amp;user_id='.$thisUser['user_id'].'" '
               . 'onClick="return confirmation(\''.clean_str_for_javascript($langUnreg .' '.$thisUser['nom'].' '.$thisUser['prenom']).'\');">'
               . '<img border="0" alt="'.$langUnreg.'" src="'.$imgRepositoryWeb.'unenroll.gif">'
               . '</a>'
               ;
        }

        echo '</td>'."\n";
    }  // END - is_allowedToEdit

    echo '</tr>'."\n";

    $previousUser = $thisUser['user_id'];

} // END - foreach users

/*----------------------------------------------------------------------
   Display table footer
  ----------------------------------------------------------------------*/

echo '</tbody>' . "\n"
    .'</table>' . "\n" ;

/*----------------------------------------------------------------------
   Display pager
  ----------------------------------------------------------------------*/

$myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);


include($includePath.'/claro_init_footer.inc.php');
?>
