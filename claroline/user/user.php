<?php // $Id$
/**
 * CLAROLINE
 *
 * This tool list user member of the course.
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2007 Universite catholique de Louvain (UCL)
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
$tlabelReq = 'CLUSR';
$gidReset = true;
require '../inc/claro_init_global.inc.php';

if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);

claro_set_display_mode_available(true);

/*----------------------------------------------------------------------
   Include Library
  ----------------------------------------------------------------------*/

require_once get_path('incRepositorySys')  . '/lib/admin.lib.inc.php';
require_once get_path('incRepositorySys')  . '/lib/user.lib.php';
require_once get_path('incRepositorySys')  . '/lib/course_user.lib.php';
require_once get_path('incRepositorySys')  . '/lib/pager.lib.php';

/*----------------------------------------------------------------------
   Load config
  ----------------------------------------------------------------------*/
include claro_get_conf_repository() . 'user_profile.conf.php';

/*----------------------------------------------------------------------
  Stats
  ----------------------------------------------------------------------*/

event_access_tool(claro_get_current_tool_id(), claro_get_current_course_tool_data('label'));

/*----------------------------------------------------------------------
   JavaScript - Delete Confirmation
  ----------------------------------------------------------------------*/

$htmlHeadXtra[] =
'
<script type="text/javascript">
function confirmation (name)
{
    if (confirm(" ' . clean_str_for_javascript(get_lang('Are you sure to delete')) . ' "+ name + " ?"))
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

$can_add_single_user = (bool) (claro_is_course_manager()
                     && get_conf('is_coursemanager_allowed_to_add_single_user') )
                     || claro_is_platform_admin();
$can_import_user_list = (bool) (claro_is_course_manager()
                     && get_conf('is_coursemanager_allowed_to_import_user_list') )
                     || claro_is_platform_admin();
$can_import_user_class = (bool) (claro_is_course_manager()
                     && get_conf('is_coursemanager_allowed_to_import_user_class') )
                     || claro_is_platform_admin();

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

/*----------------------------------------------------------------------
  Filter data
  ----------------------------------------------------------------------*/

$cmd = ( isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : '');
$offset = (int) isset($_REQUEST['offset']) ? $_REQUEST['offset'] : 0;

if (isset($_REQUEST['user_id']))
{
    if ($_REQUEST['user_id'] == 'allStudent'
                    &&  $cmd == 'unregister' ) $req['user_id'] = 'allStudent';
    elseif ( 0 < (int) $_REQUEST['user_id'] )  $req['user_id'] = (int) $_REQUEST['user_id'];
    else                                       $req['user_id'] = false;
}
/*=====================================================================
  Main section
  =====================================================================*/

$disp_tool_link = FALSE;

if ( $is_allowedToEdit )
{
    $disp_tool_link = TRUE;

    if ( $cmd == 'register' && $req['user_id'])
    {
        $done = user_add_to_course($req['user_id'], claro_get_current_course_id(), false, false, false);
        if ($done)
        {
            $dialogBox = get_lang('User registered to the course');
        }
    }

    if ( $cmd == 'unregister')
    {
        // Unregister user from course
        // (notice : it does not delete user from claroline main DB)

        if ('allStudent' == $req['user_id'])
        {
            // TODO : add a function to unenroll all users from a course
            $sql = "DELETE FROM `" . $tbl_rel_course_user . "`
                    WHERE `code_cours` = '" . addslashes(claro_get_current_course_id()) . "'
                     AND `isCourseManager` = 0";

            $unregisterdUserCount = claro_sql_query_affected_rows($sql);

            $dialogBox .= get_lang('%number student(s) unregistered from this course', array ( '%number' => $unregisterdUserCount) );
        }
        elseif ( 0 < (int)  $req['user_id'] )
        {
            // delete user from course user list
            if ( user_remove_from_course(  $req['user_id'], claro_get_current_course_id(), false, false, false) )
            {
               $dialogBox .= get_lang('The user has been successfully unregistered from course');
            }
            else
            {
                switch ( claro_failure::get_last_failure() )
                {
                    case 'cannot_unsubscribe_the_last_course_manager' :
                        $dialogBox .= get_lang('You cannot unsubscribe the last course manager of the course');
                        break;
                    case 'course_manager_cannot_unsubscribe_himself' :
                        $dialogBox .= get_lang('Course manager cannot unsubscribe himself');
                        break;
                    default :
                        $dialogBox .= get_lang('Error!! you cannot unregister a course manager');
                }
            }
        }
    } // end if isset $_REQUEST['cmd']

}    // end if allowed to edit

/*----------------------------------------------------------------------
   Get User List
  ----------------------------------------------------------------------*/

$sqlGetUsers = "SELECT `user`.`user_id`      AS `user_id`,
                       `user`.`nom`          AS `nom`,
                       `user`.`prenom`       AS `prenom`,
                       `user`.`email`        AS `email`,
                       `course_user`.`profile_id`,
                       `course_user`.`isCourseManager`,
                       `course_user`.`tutor`  AS `tutor`,
                       `course_user`.`role`   AS `role`
               FROM `" . $tbl_users . "`           AS user,
                    `" . $tbl_rel_course_user . "` AS course_user
               WHERE `user`.`user_id`=`course_user`.`user_id`
               AND   `course_user`.`code_cours`='" . addslashes(claro_get_current_course_id()) . "'";

$myPager = new claro_sql_pager($sqlGetUsers, $offset, $userPerPage);

if ( isset($_GET['sort']) )
{
    $myPager->add_sort_key( $_GET['sort'], isset($_GET['dir']) ? $_GET['dir'] : SORT_ASC );
}

$defaultSortKeyList = array ('course_user.isCourseManager' => SORT_DESC,
                             'course_user.tutor'  => SORT_DESC,
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

$userListId = array();

foreach ( $userList as $thisUser )
{
    $users[$thisUser['user_id']] = $thisUser;
    $userListId[] = $thisUser['user_id'];
}

if ( count($userListId)> 0 )
{
    $sqlGroupOfUsers = "SELECT `ug`.`user` AS `uid`,
                               `ug`.`team` AS `team`,
                               `sg`.`name` AS `nameTeam`
                        FROM `"  . $tbl_rel_users_groups . "` AS `ug`
                        LEFT JOIN `" . $tbl_groups . "` AS `sg`
                        ON `ug`.`team` = `sg`.`id`
                        WHERE `ug`.`user` IN (" . implode(",",$userListId) . ")
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


// PREPARE DISPLAY

$nameTools = get_lang('Users');

if ($can_add_single_user)
{

    // Add a user link
    $userMenu[] = claro_html_cmd_link( 'user_add.php' . claro_url_relay_context('?')
                                     , '<img src="' . get_path('imgRepositoryWeb') . 'user.gif" alt="" />'
                                     . get_lang('Add a user')
                                     )
                                     ;
}
if ($can_import_user_list)
{
    // Add CSV file of user link
    $userMenu[] = claro_html_cmd_link( 'AddCSVusers.php'
                                     . '?AddType=userTool'
                                     . claro_url_relay_context('&amp;')
                                     , '<img src="' . get_path('imgRepositoryWeb') . 'importlist.gif" alt="" />'
                                     . get_lang('Add a user list')
                                     );
}
if ($can_import_user_class)
{
    // Add a class link
    $userMenu[] = claro_html_cmd_link( 'class_add.php'
                                     . claro_url_relay_context('?')
                                     , '<img src="' . get_path('imgRepositoryWeb') . 'class.gif" alt="" />'
                                     . get_lang('Enrol class')
                                     );
}

if ($can_add_single_user)
{
    // Main group settings
    $userMenu[] = claro_html_cmd_link( '../right/profile_list.php'
                                     . claro_url_relay_context('?')
                                     , '<img src="' . get_path('imgRepositoryWeb') . 'settings.gif" alt="" />'
                                     . get_lang("Right Profile")
                                     );
}

$userMenu[] = claro_html_cmd_link( '../group/group.php'
                                 . claro_url_relay_context('?')
                                 , '<img src="' . get_path('imgRepositoryWeb') . 'group.gif" alt="" />'
                                 . get_lang('Group management')
                                 );

$userMenu[] = claro_html_cmd_link( $_SERVER['PHP_SELF']
                                 . '?cmd=unregister&amp;user_id=allStudent'
                                 . claro_url_relay_context('&amp;')
                                 , '<img src="' . get_path('imgRepositoryWeb') . 'unenroll.gif" alt="" />'
                                 . get_lang('Unregister all students')
                                 , array('onClick'=>"return confirmation('" . clean_str_for_javascript(get_lang('all students')) . "')")
                                 );

/*=====================================================================
Display section
  =====================================================================*/

// Display header

include get_path('incRepositorySys') . '/claro_init_header.inc.php';

echo claro_html_tool_title($nameTools . ' (' . get_lang('number') . ' : ' . $userTotalNb . ')',
            $is_allowedToEdit ? 'help_user.php' : FALSE);

// Display Forms or dialog box(if needed)

if ( !empty($dialogBox) ) echo claro_html_message_box($dialogBox);

// Display tool links
if ( $disp_tool_link ) echo claro_html_menu_horizontal($userMenu);

/*----------------------------------------------------------------------
   Display pager
  ----------------------------------------------------------------------*/

echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

$sortUrlList = $myPager->get_sort_url_list($_SERVER['PHP_SELF']);

/*----------------------------------------------------------------------
   Display table header
  ----------------------------------------------------------------------*/

echo '<table class="claroTable emphaseLine" width="100%" cellpadding="2" cellspacing="1" '
.    ' border="0" summary="' . get_lang('Course users list') . '">' . "\n";

echo '<thead>' . "\n"
.    '<tr class="headerX" align="center" valign="top">'."\n"
.    '<th><a href="' . $sortUrlList['nom'] . '">' . get_lang('Last name') . '</a></th>' . "\n"
.    '<th><a href="' . $sortUrlList['prenom'] . '">' . get_lang('First name') . '</a></th>'."\n"
.    '<th><a href="' . $sortUrlList['profile_id'] . '">' . get_lang('Profile') . '</a></th>'."\n"
.    '<th><a href="' . $sortUrlList['role'] . '">' . get_lang('Role') . '</a></th>'."\n"
.    '<th>' . get_lang('Group') . '</th>' . "\n" ;

if ( $is_allowedToEdit ) // EDIT COMMANDS
{
    echo '<th><a href="'.$sortUrlList['tutor'].'">'.get_lang('Group Tutor').'</a></th>'."\n"
       . '<th><a href="'.$sortUrlList['isCourseManager'].'">'.get_lang('Course manager').'</a></th>'."\n"
       . '<th>'.get_lang('Edit').'</th>'."\n"
       . '<th>'.get_lang('Unregister').'</th>'."\n" ;
}

echo '</tr>'."\n"
   . '</thead>'."\n"
   . '<tbody>'."\n" ;

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
       . '<td align="left">'
       . '<img src="' . get_path('imgRepositoryWeb') . '/user.gif" alt="" />'."\n"
       . '<small>' . $i . '</small>'."\n"
       . '&nbsp;';

    if ( $is_allowedToEdit || get_conf('linkToUserInfo') )
    {
        echo '<a href="userInfo.php?uInfo=' . $thisUser['user_id']
        .    claro_url_relay_context('&amp;') . '">'
        .    ucfirst(strtolower($thisUser['nom']))
        .    '</a>'
        ;
    }
    else
    {
        echo ucfirst(strtolower($thisUser['nom']));
    }

    echo '</td>'
    .    '<td align="left">' . $thisUser['prenom'] . '</td>'


    // User profile column
    .    '<td align="left">'
    .    claro_get_profile_name($thisUser['profile_id'])
    .    '</td>' . "\n"
    ;

    // User role column
    if ( empty($thisUser['role']) )    // NULL and not '0' because team can be inexistent
    {
        echo '<td> - </td>'."\n";
    }
    else
    {
        echo '<td>'.$thisUser['role'].'</td>'."\n";
    }

    // User group column
    if ( !isset ($usersGroup[$thisUser['user_id']]) )    // NULL and not '0' because team can be inexistent
    {
        echo '<td> - </td>'."\n";
    }
    else
    {
        $userGroups = $usersGroup[$thisUser['user_id']];
        echo '<td>'."\n";
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
        echo '<td>&nbsp;</td>'."\n";
    }
    elseif ( $is_allowedToEdit )
    {
        // Tutor column
        if($thisUser['tutor'] == '0')
        {
            echo '<td> - </td>' . "\n";
        }
        else
        {
            echo '<td>' . get_lang('Group Tutor') . '</td>' . "\n";
        }

        // course manager column
        if($thisUser['isCourseManager'] == '1')
        {
            echo '<td>' . get_lang('Course manager') . '</td>' . "\n";
        }
        else
        {
            echo '<td> - </td>' . "\n";
        }

        // Edit user column
        echo '<td>'
        .    '<a href="userInfo.php?editMainUserInfo='.$thisUser['user_id']
        .    claro_url_relay_context('&amp;') . '">'
        .    '<img border="0" alt="'.get_lang('Edit').'" src="' . get_path('imgRepositoryWeb') . '/edit.gif" />'
        .    '</a>'
        .    '</td>' . "\n"

        // Unregister user column
        .    '<td>'
        ;

        if ($thisUser['user_id'] != claro_get_current_user_id())
        {
            echo '<a href="'.$_SERVER['PHP_SELF']
            .    '?cmd=unregister&amp;user_id=' . $thisUser['user_id']
            .    claro_url_relay_context('&amp;') . '" '
            .    'onClick="return confirmation(\''.clean_str_for_javascript(get_lang('Unregister') .' '.$thisUser['nom'].' '.$thisUser['prenom']).'\');">'
            .    '<img border="0" alt="' . get_lang('Unregister') . '" src="' . get_path('imgRepositoryWeb') . '/unenroll.gif" />'
            .    '</a>'
            ;
        }

        echo '</td>' . "\n";

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

/*
if ( count($userGrid) > 0 ) echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

echo $userDataGrid->render();

if ( count($userGrid) > 0 ) echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);
*/


include get_path('incRepositorySys') . '/claro_init_footer.inc.php';
?>
