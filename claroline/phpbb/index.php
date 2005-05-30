<?php // $Id$
/**
 * CLAROLINE
 *
 * Script for forum tool
 *
 * @version 1.6 $Revision$
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

include '../inc/claro_init_global.inc.php';

$nameTools = $langForums;

if ( !isset($_cid) ) claro_disp_select_course();
if ( !isset($is_courseAllowed) || !$is_courseAllowed ) claro_disp_auth_form();

claro_set_display_mode_available(true); // view mode

/*-----------------------------------------------------------------
  Stats
 -----------------------------------------------------------------*/

include $includePath.'/lib/events.lib.inc.php';
event_access_tool($_tid, $_courseTool['label']);

/*-----------------------------------------------------------------
  Library
 -----------------------------------------------------------------*/

include $includePath . '/lib/forum.lib.php';

/*-----------------------------------------------------------------
  DB table names
 -----------------------------------------------------------------*/

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_cdb_names = claro_sql_get_course_tbl();

$tbl_course_user = $tbl_mdb_names['rel_course_user'];
$tbl_users       = $tbl_mdb_names['user'];

$tbl_categories       = $tbl_cdb_names['bb_categories'      ];
$tbl_forums           = $tbl_cdb_names['bb_forums'          ];
$tbl_group_properties = $tbl_cdb_names['group_property'     ];
$tbl_posts            = $tbl_cdb_names['bb_posts'           ];
$tbl_student_group    = $tbl_cdb_names['group_team'         ];
$tbl_user_group       = $tbl_cdb_names['group_rel_team_user'];

/*-----------------------------------------------------------------
  Initialise variables
 -----------------------------------------------------------------*/

$last_visit = $_user['lastLogin'];

/*=================================================================
  Main Section
 =================================================================*/

// Get forums categories

$sql = "SELECT `c`.`cat_id`, `c`.`cat_title`, `c`.`cat_order`
        FROM   `" . $tbl_categories . "` c, `" . $tbl_forums . "` f
        WHERE `f`.`cat_id` = `c`.`cat_id`
        GROUP BY `c`.`cat_id`, `c`.`cat_title`, `c`.`cat_order`
        ORDER BY `c`.`cat_order` ASC";

$categories       = claro_sql_query_fetch_all($sql);
$total_categories = count($categories);

// Get forums data

$sql = "SELECT f.*, u.username, u.user_id, p.post_time, g.id gid
        FROM `" . $tbl_forums . "` f
        LEFT JOIN `" . $tbl_posts . "` p 
               ON p.post_id = f.forum_last_post_id
        LEFT JOIN `" . $tbl_users . "` u 
               ON u.user_id = p.poster_id
        LEFT JOIN `" . $tbl_student_group . "` g 
               ON g.forumId = f.forum_id
        ORDER BY f.forum_order, f.cat_id, f.forum_id ";

$forum_list = claro_sql_query_fetch_all($sql);

if ( !empty($_uid) )
{
    // Get the id of groups'forum where the user have access.

    $sql = "SELECT `g`.`forumId` as `forum_id`
            FROM `" . $tbl_student_group . "` `g`,
                 `" . $tbl_user_group . "` `gu`
            WHERE `g`.`id`    = `gu`.`team`
              AND `gu`.`user` = '".$_uid."'";

    $userGroupList = claro_sql_query_fetch_all_cols($sql);
    $userGroupList = $userGroupList['forum_id'];

    // Get the id of groups'forum where the user is tutor.

    $sql = "SELECT `forumId` `forum_id`, `id` `group_id` 
            FROM `" . $tbl_student_group . "`
            WHERE tutor = '" . $_uid . "'";

    $tutorGroupList = claro_sql_query_fetch_all_cols($sql);
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
        .' <th colspan="7" class="superHeader">' . $title . '</th>' . "\n"
        .'</tr>' . "\n"
        .' <tr class="headerX" align="center">' . "\n"
        .' <th colspan="2" align="left">' . $langForum . '</th>' . "\n"
        .' <th>' . $l_topics . '</th>' . "\n"
        .' <th>' . $l_posts  . '</th>' . "\n"
        .' <th>' . $l_lastpost . '</th>' . "\n"
        .'</tr>' . "\n";

    foreach ( $forum_list as $this_forum )
    {
        if ( $this_forum['cat_id'] == $this_category['cat_id'] )
        {
            $forum_name   = htmlspecialchars(stripslashes($this_forum['forum_name']));
            $forum_desc   = htmlspecialchars(stripslashes($this_forum['forum_desc']));
            $forum_id     = $this_forum['forum_id'    ];
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

            echo '<td align="center" valign="top" width="5%">' . "\n"
                .'<img src="' . $imgRepositoryWeb . $forum_img . '">' . "\n"
                .'</td>' . "\n";


            echo '<td>' . "\n";

            // Visit only my group forum if not admin or tutor.
            // If tutor, see all groups but indicate my groups.
            // Group Category == 1

            if ( $this_category['cat_id'] == 1 )
            {
                if (   ( isset($userGroupList) && in_array($forum_id, $userGroupList) )
                    || ( isset($tutorGroupList['forum_id']) && in_array($forum_id, $tutorGroupList['forum_id']) )
                    || $is_forumAdmin
                    || ( isset($is_groupPrivate) && ! $is_groupPrivate)
                   )
                {
                    echo '<a href="viewforum.php?gidReq=' . $this_forum['gid']
                        .'&amp;forum=' . $forum_id . '">'
                        .$forum_name
                        .'</a>' ;

                    if ( in_array($forum_id, $tutorGroupList['forum_id']) )
                    {
                        echo '&nbsp;<small>(' . $langOneMyGroups . ')</small>';
                    }

                    if ( in_array($forum_id, $userGroupList) )
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
                    . $forum_name
                    . '</a> ';
            }

            echo '<br><small>' . $forum_desc . '</small>' . "\n"
                .'</td>' . "\n"

                .'<td width="5%" align="center" valign="middle">' . "\n"
                .'<small>' . $total_topics . '</small>' . "\n"
                .'</td>' . "\n"

                .'<td width="5%" align="center" valign="middle">' . "\n"
                .'<small>' . $total_posts . '<small>' . "\n"
                .'</td>' . "\n";

            if ( !empty($last_post) )
            {
                echo '<td width="15%" align="center" valign="middle">' . "\n"
                    . '<small>' . $last_post . '</small>'
                    . '</td>' . "\n";
            } 
            else
            {
                echo '<td width="15%" align="center" valign="middle">' . "\n"
                    . '<small>' . $langNoPost . '</small>'
                    . '</td>' . "\n";
            }
            echo '</tr>' . "\n";
        }
    }
}

echo '</table>' . "\n";

// Display Forum Footer

echo  '<br />
<center>
<small>Copyright &copy; 2000 - 2001 <a href="http://www.phpbb.com/" target="_blank">The phpBB Group</a></small>
</center>';

include($includePath.'/claro_init_footer.inc.php');

?>
