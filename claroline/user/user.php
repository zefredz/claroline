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

if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);

claro_set_display_mode_available(true);

/*----------------------------------------------------------------------
   Include Library
  ----------------------------------------------------------------------*/

include($includePath  . '/lib/admin.lib.inc.php');
include($includePath  . '/lib/user.lib.php');
include($includePath  . '/conf/user_profile.conf.php');
include($includePath  . '/lib/pager.lib.php');
@include($includePath . '/lib/debug.lib.inc.php');

/*----------------------------------------------------------------------
  Stats
  ----------------------------------------------------------------------*/

event_access_tool($_tid, $_courseTool['label']);

/*----------------------------------------------------------------------
   JavaScript - Delete Confirmation
  ----------------------------------------------------------------------*/

$htmlHeadXtra[] =
'
<script type="text/javascript">
function confirmation (name)
{
    if (confirm(" ' . clean_str_for_javascript(get_lang('AreYouSureToDelete')) . ' "+ name + " ?"))
        {return true;}
    else
        {return false;}
}
</script>
';

/*----------------------------------------------------------------------
   Variables
  ----------------------------------------------------------------------*/

$userPerPage = get_conf('nbUsersPerPage',50);

$is_allowedToEdit = claro_is_allowed_to_edit();

$can_add_user     = (bool) (   $is_courseAdmin
                     && get_conf('is_coursemanager_allowed_to_add_user') )
                     || $is_platformAdmin;

$currentCourse = $currentCourseID  = $_course['sysCode'];

$dialogBox = '';

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

$disp_tool_link = FALSE;

$cmd = ( isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : '');

if ( $is_allowedToEdit )
{
    $disp_tool_link = TRUE;

    if ( $cmd == 'register')
    {
        $user_id   = $_REQUEST['user_id'];
        $done = user_add_to_course($user_id, $_cid);
        if ($done)
        {
            $dialogBox = get_lang('UserRegisteredToCourse');
        }
    }

    if ( $cmd == 'unregister')
    {
        // Unregister user from course
        // (notice : it does not delete user from claroline main DB)

        if ($_REQUEST['user_id'] == 'allStudent')
        {
            $sql = "DELETE FROM `" . $tbl_rel_course_user . "`
                    WHERE `code_cours` = '" . addslashes($currentCourseID) . "'
                     AND `statut` = 5";

            $unregisterdUserCount = claro_sql_query_affected_rows($sql);

            $dialogBox .= sprintf(get_lang('_p_d_StudentUnregistredFormCours'),$unregisterdUserCount);
        }
        elseif ( 0 < (int)$_REQUEST['user_id'] )
        {
            // delete user from course user list
            if ( user_remove_from_course( $_REQUEST['user_id'], $_cid) )
            {
               $dialogBox .= get_lang('UserUnsubscribedFromCourse');
            }
            else
            {
                switch ( claro_failure::get_last_failure() )
                {
                    case 'cannot_unsubscribe_the_last_course_manager' :
                        $dialogBox .= get_lang('CannotUnsubscribeLastCourseManager');
                        break;
                    case 'course_manager_cannot_unsubscribe_himself' :
                        $dialogBox .= get_lang('CourseManagerCannotUnsubscribeHimself');
                        break;
                    default :
                        $dialogBox .= get_lang('UserNotUnsubscribedFromCourse');
                }
            }
        }
    } // end if isset $_REQUEST['cmd']

}    // end if allowed to edit

/*----------------------------------------------------------------------
   Get User List
  ----------------------------------------------------------------------*/

$sqlGetUsers ='SELECT `user`.`user_id`, `user`.`nom`, `user`.`prenom`,
                      `user`.`email`, `cours_user`.`statut`,
                      `cours_user`.`tutor`, `cours_user`.`role`
               FROM `'.$tbl_users.'` user, `'.$tbl_rel_course_user.'` cours_user
               WHERE `user`.`user_id`=`cours_user`.`user_id`
               AND   `cours_user`.`code_cours`="'. addslashes($currentCourseID) .'"';

$offset = isset($_REQUEST['offset']) ? $_REQUEST['offset'] : 0;

$myPager     = new claro_sql_pager($sqlGetUsers, $offset, $userPerPage);

if ( isset($_GET['sort']) )
{
    $myPager->add_sort_key( $_GET['sort'], isset($_GET['dir']) ? $_GET['dir'] : SORT_ASC );
}

$defaultSortKeyList = array ('cours_user.statut' => SORT_ASC,
                             'cours_user.tutor'  => SORT_DESC,
                             'user.nom'          => SORT_ASC,
                             'user.prenom'       => SORT_ASC);

foreach($defaultSortKeyList as $thisSortKey => $thisSortDir)
{
    $myPager->add_sort_key( $thisSortKey, $thisSortDir);

}

$userList    = $myPager->get_result_list();
$userTotalNb = $myPager->get_total_item_count();

/*----------------------------------------------------------------------
  Get groups
  ----------------------------------------------------------------------*/

$usersId = array();

foreach ( $userList as $thisUser )
{
    $users[$thisUser['user_id']]    = $thisUser;
    $usersId[]    = $thisUser['user_id'];
}

if ( count($usersId)> 0 )
{
    $sqlGroupOfUsers = "SELECT `ug`.`user` AS `uid`, `ug`.`team` AS `team`,
                        `sg`.`name` AS `nameTeam`
                        FROM `".$tbl_rel_users_groups."` `ug`
                        LEFT JOIN `".$tbl_groups."` `sg`
                        ON `ug`.`team` = `sg`.`id`
                        WHERE `ug`.`user` IN (".implode(",",$usersId).")
                        ORDER BY `sg`.`name`";

    $userGroupList = claro_sql_query_fetch_all($sqlGroupOfUsers);

    $usersGroup = array();

    if( is_array($userGroupList) && !empty($userGroupList) )
    {
        foreach( $userGroupList as $thisAffiliation )
        {
            $usersGroup[$thisAffiliation['uid']][$thisAffiliation['team']]['nameTeam'] = $thisAffiliation['nameTeam'];
        }
    }
}

/*=====================================================================
  Display section
  =====================================================================*/

$nameTools = get_lang('Users');

// Display header

include $includePath . '/claro_init_header.inc.php';

echo claro_disp_tool_title($nameTools.' ('.get_lang('UserNumber').' : '.$userTotalNb.')',
            $is_allowedToEdit ? 'help_user.php' : FALSE);

// Display Forms or dialog box(if needed)

if ( !empty($dialogBox) )
{
    echo claro_disp_message_box($dialogBox);
}

// Display tool links

if ( $disp_tool_link )
{
    echo '<p>';
    if ($can_add_user)
    {
       //add a user link
    ?>
    <a class="claroCmd" href="user_add.php"><img src="<?php echo $imgRepositoryWeb; ?>user.gif" alt="" /><?php echo get_lang('AddAU'); ?></a> |
    <?php
       //add CSV file of user link
    ?>
    <a class="claroCmd" href="AddCSVusers.php?AddType=userTool"><img src="<?php echo $imgRepositoryWeb; ?>importlist.gif" alt="" /> <?php echo get_lang('AddCSVUsers'); ?></a> |
    <?php
       //add a class link
    ?>
    <a class="claroCmd" href="class_add.php"><img src="<?php echo $imgRepositoryWeb; ?>class.gif" alt="" /> <?php echo get_lang('EnrollClass'); ?></a> |
    <?php

    }
    ?>
    <a class="claroCmd" href="../group/group.php"><img src="<?php echo $imgRepositoryWeb; ?>group.gif" alt="" /><?php echo get_lang('GroupUserManagement'); ?></a> |

    <a class="claroCmd" href="<?php echo $_SERVER['PHP_SELF']; ?>?cmd=unregister&amp;user_id=allStudent"
       onClick="return confirmation('<?php echo clean_str_for_javascript(' all students '); ?>')">
    <img src="<?php echo $imgRepositoryWeb; ?>unenroll.gif" alt="" /><?php echo get_lang('UnregisterAllStudents') ?>
    </a>
    </p>
<?php
}

/*----------------------------------------------------------------------
   Display pager
  ----------------------------------------------------------------------*/

echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);


$sortUrlList = $myPager->get_sort_url_list($_SERVER['PHP_SELF']);

/*----------------------------------------------------------------------
   Display table header
  ----------------------------------------------------------------------*/

echo '<table class="claroTable emphaseLine" '
.    ' width="100%" cellpadding="2" cellspacing="1" '
.    ' border="0" summary="' . get_lang('ListCourseUsers') . '">' . "\n"
.    '<colgroup span="4" align="left"></colgroup>' . "\n"
;

    if($is_allowedToEdit)
    {
        echo '<colgroup span="2"></colgroup>'."\n"
           . '<colgroup span="2" width="0" ></colgroup>'."\n"
           ;
    }

    echo '<thead>'."\n"
       . '<tr class="headerX" align="center" valign="top">'."\n"
       . '<th scope="col" id="lastname"><a href="'.$sortUrlList['nom'].'">'.get_lang('LastName').'</a></th>'."\n"
       . '<th scope="col" id="firstname"><a href="'.$sortUrlList['prenom'].'">'.get_lang('FirstName').'</a></th>'."\n"
       . '<th scope="col" id="role"><a href="'.$sortUrlList['role'].'">'.get_lang('Role').'</a></th>'."\n"
       . '<th scope="col" id="team">'.get_lang('Group').'</th>'."\n"
       ;

    if($is_allowedToEdit) // EDIT COMMANDS
    {
        echo '<th scope="col" id="tut"  ><a href="'.$sortUrlList['tutor'].'">'.get_lang('GroupTutor').'</a></th>'."\n"
           . '<th scope="col" id="CM"   ><a href="'.$sortUrlList['statut'].'">'.get_lang('CourseManager').'</a></th>'."\n"
           . '<th scope="col" id="edit" >'.get_lang('Edit').'</th>'."\n"
           . '<th scope="col" id="del"  >'.get_lang('Unreg').'</th>'."\n"
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
       . '<img src="'.$imgRepositoryWeb.'user.gif" alt="" />'."\n"
       . '<small>' . $i . '</small>'."\n"
       . '&nbsp;';

    if ( $is_allowedToEdit || get_conf('linkToUserInfo') )
    {
        echo '<a href="userInfo.php?uInfo='.$thisUser['user_id'].'">'
            . ucfirst(strtolower($thisUser['nom']))
            . '</a>';
    }
    else
    {
        echo ucfirst(strtolower($thisUser['nom']));
    }
    
    echo '</td>';

    echo '<td align="left">'.$thisUser['prenom'].'</td>';

    // User role column
    echo '<td headers="role u'.$i.'" align="left">'.$thisUser['role'].'</td>'."\n";

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
               . ' <small>('.$thisGroupsNo.')</small>'
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
            echo '<td headers="tut u'.$i.'">'.get_lang('GroupTutor').'</td>'."\n";
        }

        // course manager column
        if($thisUser['statut'] == '1')
        {
            echo '<td headers="CM u'.$i.'">'.get_lang('CourseManager').'</td>'."\n";
        }
        else
        {
            echo '<td headers="CM u'.$i.'"> - </td>'."\n";
        }

        // Edit user column
        echo '<td headers="edit u'.$i.'">'
           . '<a href="userInfo.php?editMainUserInfo='.$thisUser['user_id'].'">'
           . '<img border="0" alt="'.get_lang('Edit').'" src="'.$imgRepositoryWeb.'edit.gif" />'
           . '</a>'
           . '</td>'."\n"
        // Unregister user column
           . '<td headers="del u'.$i.'" >';

        if ($thisUser['user_id'] != $_uid)
        {
            echo '<a href="'.$_SERVER['PHP_SELF'].'?cmd=unregister&amp;user_id='.$thisUser['user_id'].'" '
            .    'onClick="return confirmation(\''.clean_str_for_javascript(get_lang('Unreg') .' '.$thisUser['nom'].' '.$thisUser['prenom']).'\');">'
            .    '<img border="0" alt="'.get_lang('Unreg').'" src="'.$imgRepositoryWeb.'unenroll.gif" />'
            .    '</a>'
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
.    '</table>' . "\n"
;

/*----------------------------------------------------------------------
   Display pager
  ----------------------------------------------------------------------*/

echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);


include $includePath . '/claro_init_footer.inc.php';
?>
