<?php // $Id$
/**
 * CLAROLINE
 *
 * Script for forum tool
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 * @copyright (C) 2001 The phpBB Group
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package CLFRM
 *
 */

/*=================================================================
  Init Section
 =================================================================*/

$tlabelReq = 'CLFRM___';

require '../inc/claro_init_global.inc.php';

$nameTools = $langForums;

if ( !isset($_cid) ) claro_disp_select_course();
if ( !isset($is_courseAllowed) || !$is_courseAllowed ) claro_disp_auth_form();

claro_set_display_mode_available(true); // view mode

/*-----------------------------------------------------------------
  Stats
 -----------------------------------------------------------------*/

include $includePath . '/lib/events.lib.inc.php';
event_access_tool($_tid, $_courseTool['label']);

/*-----------------------------------------------------------------
  Library
 -----------------------------------------------------------------*/

include $includePath . '/lib/forum.lib.php';

/*-----------------------------------------------------------------
  Initialise variables
 -----------------------------------------------------------------*/

$last_visit = $_user['lastLogin'];
$is_allowedToEdit = $is_courseAdmin || $is_platformAdmin;

/*=================================================================
  Main Section
 =================================================================*/

/*-----------------------------------------------------------------
  Administration command
 -----------------------------------------------------------------*/

if ( $is_allowedToEdit )
{

    $cmd = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : '';
    
    if ( $cmd = 'emptyForum' && isset($_REQUEST['id']) )
    {
        delete_all_post_in_forum($_REQUEST['id']);
    }
        
}

/*-----------------------------------------------------------------
  Get forums categories
 -----------------------------------------------------------------*/

$categories       = get_category_list();
$total_categories = count($categories);

$forum_list = get_forum_list();

if ( !empty($_uid) )
{
    $userGroupList  = get_user_group_list($_uid);
    $tutorGroupList = get_tutor_group_list($_uid);
}

/*=================================================================
  Display Section
 =================================================================*/

// Claroline Header

include $includePath . '/claro_init_header.inc.php';

$pagetitle = $l_indextitle;
$pagetype  = 'index';

$is_allowedToEdit = claro_is_allowed_to_edit() 
                    || ( $is_groupTutor && !$is_courseAdmin);
                    // ( $is_groupTutor 
                    //  is added to give admin status to tutor 
                    // && !$is_courseAdmin)
                    // is added  to let course admin, tutor of current group, use student mode
                     
$is_forumAdmin    = claro_is_allowed_to_edit();

claro_disp_tool_title($langForums, 
                      $is_allowedToEdit ? 'help_forum.php' : false);

// Forum toolbar

disp_forum_toolbar($pagetype, 0, 0, 0);

/*-----------------------------------------------------------------
  Display Forum Index Page
------------------------------------------------------------------*/

echo '<table width="100%" class="claroTable emphaseLine">' . "\n";

foreach ( $categories as $this_category )
{

    $title = htmlspecialchars($this_category['cat_title']);

    // Category banner

    echo '<tr align="left" valign="top">' . "\n"
    .    ' <th colspan="7" class="superHeader">' . $title . '</th>' . "\n"
    .    '</tr>' . "\n"
    .    ' <tr class="headerX" align="center">' . "\n"
    .    ' <th align="left">' . $langForum . '</th>' . "\n"
    .    ' <th>' . $l_topics . '</th>' . "\n"
    .    ' <th>' . $l_posts  . '</th>' . "\n"
    .    ' <th>' . $l_lastpost . '</th>' . "\n"
    .    '</tr>' . "\n"
    ;

    foreach ( $forum_list as $this_forum )
    {
        if ( $this_forum['cat_id'] == $this_category['cat_id'] )
        {
            $forum_name   = htmlspecialchars($this_forum['forum_name']);
            $forum_desc   = htmlspecialchars($this_forum['forum_desc']);
            $forum_id     = $this_forum['forum_id'    ];
            $group_id     = $this_forum['group_id'    ];
            $total_topics = $this_forum['forum_topics'];
            $total_posts  = $this_forum['forum_posts' ];
            $last_post    = $this_forum['post_time'   ];

            echo '<tr align="left" valign="top">' . "\n";

            if ( ! is_null($last_post) && datetime_to_timestamp($last_post) > $last_visit )
            {
                $forum_img = 'forum_hot.gif';
            }
            else
            {
                $forum_img = 'forum.gif';
            }

            echo '<td>'                                               . "\n"
            .    '<img src="' . $imgRepositoryWeb . $forum_img . '">' . "\n"
            .    '&nbsp;'                                             . "\n"
            ;

            // Visit only my group forum if not admin or tutor.
            // If tutor, see all groups but indicate my groups.
            // Group Category == 1

            if ( $this_category['cat_id'] == 1 )
            {
                if (   (is_array($userGroupList) && in_array($group_id, $userGroupList ) )
                    || (is_array($tutorGroupList) &&in_array($group_id, $tutorGroupList) )
                    || $is_forumAdmin
                    || ( isset($is_groupPrivate) && ! $is_groupPrivate)
                   )
                {
                    echo '<a href="viewforum.php?gidReq=' . $this_forum['gid']
                    .    '&amp;forum=' . $forum_id . '">'
                    .    $forum_name
                    .    '</a>' 
                    ;

                    if ( is_array($tutorGroupList) && in_array($group_id, $tutorGroupList) )
                    {
                        echo '&nbsp;<small>(' . $langOneMyGroups . ')</small>';
                    }

                    if ( is_array($userGroupList) && in_array($group_id, $userGroupList) )
                    {
                        echo '&nbsp;<small>(' . $langMyGroup . ')</small>';
                    }
                }
                else
                {
                    echo $forum_name;
                }
            }
            else
            {
                echo '<a href="viewforum.php?forum=' . $forum_id . '">'
                .    $forum_name
                .    '</a> '
                ;
            }

            echo '<br><small>' . $forum_desc . '</small>' . "\n"
            .    '</td>' . "\n"

            .    '<td width="5%" align="center" valign="middle">' . "\n"
            .    '<small>' . $total_topics . '</small>' . "\n"
            .    '</td>' . "\n"

            .    '<td width="5%" align="center" valign="middle">' . "\n"
            .    '<small>' . $total_posts . '<small>' . "\n"
            .    '</td>' . "\n"
            ;

            if ( !empty($last_post) )
            {
                echo '<td width="15%" align="center" valign="middle">' . "\n"
                .    '<small>' . $last_post . '</small>'
                .    '</td>' . "\n"
                ;
            } 
            else
            {
                echo '<td width="15%" align="center" valign="middle">' . "\n"
                .    '<small>' . $langNoPost . '</small>'
                .    '</td>' . "\n"
                ;
            }
            echo '</tr>' . "\n";
        }
    }
}

echo '</table>' . "\n"

// Display Forum Footer

.     '<br>'
.     '<center>'
.     '<small>'
.     'Copyright &copy; 2000 - 2001 <a href="http://www.phpbb.com/" target="_blank">The phpBB Group</a>'
.     '</small>'
.     '</center>'
;

include($includePath.'/claro_init_footer.inc.php');

?>