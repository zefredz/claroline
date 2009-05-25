<?php // $Id$

/**
 * User list tool
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2007 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2.0
 * @package     CLUSR
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
require_once dirname(__FILE__) . '/../messaging/lib/permission.lib.php';

/*----------------------------------------------------------------------
   Load config
  ----------------------------------------------------------------------*/
include claro_get_conf_repository() . 'user_profile.conf.php';

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
                     && get_conf('is_coursemanager_allowed_to_enroll_single_user') )
                     || claro_is_platform_admin();
$can_import_user_list = (bool) (claro_is_course_manager()
                     && get_conf('is_coursemanager_allowed_to_import_user_list') )
                     || claro_is_platform_admin();
$can_export_user_list = (bool) (claro_is_course_manager()
                     && get_conf('is_coursemanager_allowed_to_export_user_list', true) )
                     || claro_is_platform_admin();

$can_import_user_class = (bool) (claro_is_course_manager()
                     && get_conf('is_coursemanager_allowed_to_import_user_class') )
                     || claro_is_platform_admin();
$can_send_message_to_course = current_user_is_allowed_to_send_message_to_current_course();

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
                    WHERE `code_cours` = '" . claro_sql_escape(claro_get_current_course_id()) . "'
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
    } // end if cmd == unregister

    if( $cmd == 'export' && $can_export_user_list )
    {
        require_once( dirname(__FILE__) . '/lib/export.lib.php');

        // contruction of XML flow
        $csv = export_user_list(claro_get_current_course_id());

        if( !empty($csv) )
        {
            header("Content-type: application/csv");
            header('Content-Disposition: attachment; filename="'.claro_get_current_course_id().'_userlist.csv"');
            echo $csv;
            exit;
        }
    }
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
               AND   `course_user`.`code_cours`='" . claro_sql_escape(claro_get_current_course_id()) . "'";

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
    $userMenu[] = claro_html_cmd_link( htmlspecialchars(Url::Contextualize(get_module_url('CLUSR').'/user_add.php'))
                                     , '<img src="' . get_icon_url('user') . '" alt="" />'
                                     . get_lang('Add a user')
                                     )
                                     ;
}

if ($can_import_user_list)
{
    // Add CSV file of user link
    $userMenu[] = claro_html_cmd_link( htmlspecialchars(Url::Contextualize(
                                        get_module_url('CLUSR').'/AddCSVusers.php'
                                         . '?AddType=userTool'))
                                     , '<img src="' . get_icon_url('import_list') . '" alt="" />'
                                     . get_lang('Add a user list')
                                     );
}

if ($can_export_user_list)
{
    // Export CSV file of user link
    $userMenu[] = claro_html_cmd_link( htmlspecialchars(Url::Contextualize(
                                        $_SERVER['PHP_SELF'] . '?cmd=export' ))
                                     , '<img src="' . get_icon_url('export') . '" alt="" />'
                                     . get_lang('Export user list')
                                     );
}

if ($can_import_user_class)
{
    // Add a class link
    $userMenu[] = claro_html_cmd_link( htmlspecialchars(Url::Contextualize(
                                        get_module_url('CLUSR') . '/class_add.php' ))
                                     , '<img src="' . get_icon_url('class') . '" alt="" />'
                                     . get_lang('Enrol class')
                                     );
}

if ($can_send_message_to_course)
{
    // Main group settings
    $userMenu[] = claro_html_cmd_link( htmlspecialchars(Url::Contextualize(
                                     get_path('clarolineRepositoryWeb') . 'messaging/sendmessage.php?cmd=rqMessageToCourse' ))
                                     , '<img src="' . get_icon_url('mail_send') . '" alt="" />'
                                     . get_lang("Send a message to the course")
                                     );
}

$userMenu[] = claro_html_cmd_link( htmlspecialchars(Url::Contextualize( get_module_entry_url('CLGRP') ))
                                 , '<img src="' . get_icon_url('group') . '" alt="" />'
                                 . get_lang('Group management')
                                 );

$userMenu[] = claro_html_cmd_link( htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF']
                                 . '?cmd=unregister&amp;user_id=allStudent' ))
                                 , '<img src="' . get_icon_url('unenroll') . '" alt="" />'
                                 . get_lang('Unregister all students')
                                 , array('onclick'=>"return confirmation('" . clean_str_for_javascript(get_lang('all students')) . "')")
                                 );

/*=====================================================================
Display section
  =====================================================================*/

$out = '';

$out .= claro_html_tool_title($nameTools . ' (' . get_lang('number') . ' : ' . $userTotalNb . ')',
            $is_allowedToEdit ? 'help_user.php' : FALSE);

// Display Forms or dialog box(if needed)

if ( !empty($dialogBox) ) $out .= claro_html_message_box($dialogBox);

// Display tool links
if ( $disp_tool_link ) $out .= claro_html_menu_horizontal($userMenu);

/*----------------------------------------------------------------------
   Display pager
  ----------------------------------------------------------------------*/

$out .= $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

$sortUrlList = $myPager->get_sort_url_list($_SERVER['PHP_SELF']);

/*----------------------------------------------------------------------
   Display table header
  ----------------------------------------------------------------------*/

$out .= '<table class="claroTable emphaseLine" width="100%" cellpadding="2" cellspacing="1" '
.    ' border="0" summary="' . get_lang('Course users list') . '">' . "\n";

$out .= '<thead>' . "\n"
.    '<tr class="headerX" align="center" valign="top">'."\n"
.    '<th><a href="' . htmlspecialchars(Url::Contextualize($sortUrlList['nom'])) . '">' . get_lang('Last name') . '</a></th>' . "\n"
.    '<th><a href="' . htmlspecialchars(Url::Contextualize($sortUrlList['prenom'])) . '">' . get_lang('First name') . '</a></th>'."\n"
.    '<th><a href="' . htmlspecialchars(Url::Contextualize($sortUrlList['profile_id'])) . '">' . get_lang('Profile') . '</a></th>'."\n"
.    '<th><a href="' . htmlspecialchars(Url::Contextualize($sortUrlList['role'])) . '">' . get_lang('Role') . '</a></th>'."\n"
.    '<th>' . get_lang('Group') . '</th>' . "\n" ;

if ( $is_allowedToEdit ) // EDIT COMMANDS
{
    $out .= '<th><a href="'.htmlspecialchars(Url::Contextualize($sortUrlList['tutor'])).'">'.get_lang('Group Tutor').'</a></th>'."\n"
       . '<th><a href="'.htmlspecialchars(Url::Contextualize($sortUrlList['isCourseManager'])).'">'.get_lang('Course manager').'</a></th>'."\n"
       . '<th>'.get_lang('Edit').'</th>'."\n"
       . '<th>'.get_lang('Unregister').'</th>'."\n" ;
}

$out .= '</tr>'."\n"
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
    $out .= '<tr align="center" valign="top">'."\n"
       . '<td align="left">'
       . '<img src="' . get_icon_url('user') . '" alt="" />'."\n"
       . '<small>' . $i . '</small>'."\n"
       . '&nbsp;';

    if ( $is_allowedToEdit || get_conf('linkToUserInfo') )
    {
        $out .= '<a href="'.htmlspecialchars(Url::Contextualize( get_module_url('CLUSR') . '/userInfo.php?uInfo=' . (int) $thisUser['user_id'] )) . '">'
        .    htmlspecialchars( ucfirst(strtolower($thisUser['nom'])) )
        .    '</a>'
        ;
    }
    else
    {
        $out .= htmlspecialchars( ucfirst(strtolower($thisUser['nom']) ) );
    }

    $out .= '</td>'
    .    '<td align="left">' . htmlspecialchars( $thisUser['prenom'] ) . '</td>'


    // User profile column
    .    '<td align="left">'
    .    claro_get_profile_name($thisUser['profile_id'])
    .    '</td>' . "\n"
    ;

    // User role column
    if ( empty($thisUser['role']) )    // NULL and not '0' because team can be inexistent
    {
        $out .= '<td> - </td>'."\n";
    }
    else
    {
        $out .= '<td>'.htmlspecialchars( $thisUser['role'] ).'</td>'."\n";
    }

    // User group column
    if ( !isset ($usersGroup[$thisUser['user_id']]) )    // NULL and not '0' because team can be inexistent
    {
        $out .= '<td> - </td>'."\n";
    }
    else
    {
        $userGroups = $usersGroup[$thisUser['user_id']];
        $out .= '<td>'."\n";
        reset($userGroups);
        while (list($thisGroupsNo,$thisGroupsName)=each($userGroups))
        {
            $out .= '<div>'
               . htmlspecialchars( $thisGroupsName["nameTeam"] )
               . ' <small>('.htmlspecialchars( $thisGroupsNo ).')</small>'
               . '</div>';
        }
        $out .= '</td>'."\n";
    }

    if ($previousUser == $thisUser['user_id'])
    {
        $out .= '<td>&nbsp;</td>'."\n";
    }
    elseif ( $is_allowedToEdit )
    {
        // Tutor column
        if($thisUser['tutor'] == '0')
        {
            $out .= '<td> - </td>' . "\n";
        }
        else
        {
            $out .= '<td>' . get_lang('Group Tutor') . '</td>' . "\n";
        }

        // course manager column
        if($thisUser['isCourseManager'] == '1')
        {
            $out .= '<td>' . get_lang('Course manager') . '</td>' . "\n";
        }
        else
        {
            $out .= '<td> - </td>' . "\n";
        }

        // Edit user column
        $out .= '<td>'
        .    '<a href="' . htmlspecialchars(Url::Contextualize( get_module_url('CLUSR') . '/userInfo.php?editMainUserInfo='.$thisUser['user_id']))
        . '">'
        .    '<img alt="'.get_lang('Edit').'" src="' . get_icon_url('edit') . '" />'
        .    '</a>'
        .    '</td>' . "\n"

        // Unregister user column
        .    '<td>'
        ;

        if ($thisUser['user_id'] != claro_get_current_user_id())
        {
            $out .= '<a href="'.htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF']
            .    '?cmd=unregister&amp;user_id=' . $thisUser['user_id'] )) . '" '
            .    'onclick="return confirmation(\''.clean_str_for_javascript(get_lang('Unregister') .' '.$thisUser['nom'].' '.$thisUser['prenom']).'\');">'
            .    '<img alt="' . get_lang('Unregister') . '" src="' . get_icon_url('unenroll') . '" />'
            .    '</a>'
            ;
        }
        else
        {
            $out .= '&nbsp;';
        }

        $out .= '</td>' . "\n";

    }  // END - is_allowedToEdit

    $out .= '</tr>'."\n";

    $previousUser = $thisUser['user_id'];

} // END - foreach users

/*----------------------------------------------------------------------
   Display table footer
  ----------------------------------------------------------------------*/

$out .= '</tbody>' . "\n"
.    '</table>' . "\n"
;

$out .= $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);


$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>