<?php // $Id$
/**
 * CLAROLINE
 *
 * Script displays topics list of a forum
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
if ( !isset($is_courseAllowed) || $is_courseAllowed == FALSE ) claro_disp_auth_form();

claro_set_display_mode_available(true);

/*-----------------------------------------------------------------
  Stats
 -----------------------------------------------------------------*/

include $includePath . '/lib/events.lib.inc.php';
event_access_tool($_tid, $_courseTool['label']);

/*-----------------------------------------------------------------
  Library
 -----------------------------------------------------------------*/

include $includePath . '/lib/forum.lib.php';
require $includePath . '/lib/pager.lib.php';

/*-----------------------------------------------------------------
  DB table names
 -----------------------------------------------------------------*/

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_cdb_names = claro_sql_get_course_tbl();

$tbl_forums           = $tbl_cdb_names['bb_forums'];
$tbl_topics           = $tbl_cdb_names['bb_topics'];

$tbl_posts            = $tbl_cdb_names['bb_posts'];
$tbl_posts_text       = $tbl_cdb_names['bb_posts_text'];

$tbl_group_properties = $tbl_cdb_names['group_property'];
$tbl_student_group	  = $tbl_cdb_names['group_team'];
$tbl_user_group       = $tbl_cdb_names['group_rel_team_user'];
$tbl_course_user      = $tbl_mdb_names['rel_course_user'];
$tbl_group_properties = $tbl_cdb_names['group_property'];

/*-----------------------------------------------------------------
  Initialise variables
 -----------------------------------------------------------------*/

$last_visit = $_user['lastLogin'];
$error = FALSE;
$allowed = TRUE;
$error_message = '';

/*=================================================================
  Main Section
 =================================================================*/

// Get params

if ( isset($_REQUEST['forum']) ) $forum_id = (int) $_REQUEST['forum'];
else                             $forum_id = 0;

if ( !empty($_REQUEST['start']) ) $start = (int) $_REQUEST['start'];
else                              $start = 0;

$forum_exists = does_exists($forum_id, 'forum');

if ( $forum_exists )
{
    // Get forum settings
    
    $forumSettingList = get_forum_settings($forum_id);
    
    $forum_name = own_stripslashes($forumSettingList['forum_name']);
    $forum_cat_id = $forumSettingList['cat_id'];
    
    /* 
     * Check if the forum isn't attached to a group,  or -- if it is attached --, 
     * check the user is allowed to see the current group forum.
     */
    
    if (   ! is_null($forumSettingList['idGroup']) 
        && ( $forumSettingList['idGroup'] != $_gid || ! $is_groupAllowed) )
    {
        // user are not allowed to see topics of this group
        $allowed = false;
        $error_message = $langNotAllowed;
    } 

    if ( $allowed )
    {  
        // Get topics list
        
        $sql = "SELECT    t.*, p.post_time
                FROM      `".$tbl_topics."` t
                LEFT JOIN `".$tbl_posts."` p 
                       ON t.topic_last_post_id = p.post_id
                WHERE     t.forum_id = '". $forum_id ."'
                ORDER BY  topic_time DESC";
        
        $topicPager = new claro_sql_pager($sql, $start, $topics_per_page);
        $topicPager->set_pager_call_param_name('start');
        $topicList  = $topicPager->get_result_list();
        
        $pagerUrl = 'viewforum.php?forum=' . $forum_id . '&gidReq='.$_gid;
        
        /*================================================
          RELATE TO GROUP DOCUMENT AND SPACE FOR CLAROLINE
          ================================================*/
    
        // Check which group and which forum user is a member of
    
        $sqlFindTeamUser = "SELECT team, forumId, tutor, secretDirectory
                         FROM  `".$tbl_student_group."` s, `".$tbl_user_group."` u
                         WHERE u.user=\"".$_uid."\"
                         AND   s.id = u.team";
    
        $findTeamUser = claro_sql_query($sqlFindTeamUser);
    
        while ($myTeamUser = mysql_fetch_array($findTeamUser))
        {
    	    $myTeam          = $myTeamUser['team'           ];
        	$myGroupForum    = $myTeamUser['forumId'        ];
    	    $myTutor         = $myTeamUser['tutor'          ];
        	$secretDirectory = $myTeamUser['secretDirectory'];
        }
    }   
}
else
{
    // No forum
    $allowed = false;
    $error_message = $langNotAllowed;
}

/*=================================================================
  Display Section
 =================================================================*/

include $includePath . '/claro_init_header.inc.php';

if ( !$allowed )
{
    claro_disp_message_box($error_message);
}
else
{
    /*-----------------------------------------------------------------
      Display Forum Header
    -----------------------------------------------------------------*/
    
    $pagetitle = $l_viewforum;
    $pagetype = 'viewforum';
    
    $is_allowedToEdit = claro_is_allowed_to_edit() 
                        || ( $is_groupTutor && !$is_courseAdmin);
                        // ( $is_groupTutor 
                        //  is added to give admin status to tutor 
                        // && !$is_courseAdmin)
                        // is added  to let course admin, tutor of current group, use student mode
    
    claro_disp_tool_title($langForums, 
                          $is_allowedToEdit ? 'help_forum.php' : false);
    
    
    // Show Group Documents and Group Space
    // only if in Category 2 = Group Forums Category
    
    if ( $forum_cat_id == 1 && $forum_id == $myGroupForum )
    {
    	// group space links
        disp_forum_group_toolbar();
    }
    
    disp_forum_toolbar($pagetype, $forum_id, $forum_cat_id, 0);
    
    disp_forum_breadcrumb($pagetype, $forum_id, $forum_name);
    
    $topicPager->disp_pager_tool_bar($pagerUrl);
    
    echo '<table class="claroTable emphaseLine" width="100%">' . "\n"
    
        .' <tr class="superHeader">' . "\n"
        .'  <th colspan="6">' . $forum_name . '</th>' . "\n"
        .' </tr>' . "\n"
    
        .' <tr class="headerX" align="left">' . "\n"
        .'  <th colspan="2">&nbsp;' . $l_topic . '</th>' . "\n"
        .'  <th width="9%"  align="center">' . $l_posts . '</th>' . "\n"
        .'  <th width="20%" align="center">&nbsp;' . $l_poster . '</th>' . "\n"
        .'  <th width="8%"  align="center">' . $langSeen . '</th>' . "\n"
        .'  <th width="15%" align="center">' . $langLastMsg . '</th>' . "\n"
        .' </tr>' . "\n";
    
    $topics_start = $start;
    
    if ( count($topicList) == 0 )
    {
        echo ' <tr>' . "\n" 
            .'  <td colspan="6" align="center">' . $l_notopics . '</td>'. "\n"
            .' </tr>' . "\n";
    }
    else 
    {
        foreach ( $topicList as $thisTopic )
        {
            echo ' <tr>' . "\n";
    
            $replys         = $thisTopic['topic_replies'];
            $topic_time     = $thisTopic['topic_time'];
            $last_post_time = $thisTopic['post_time'];
            $last_post      = $thisTopic['post_time'];
    
            if ( empty($last_post_time) ) 
            {
                $last_post_time = datetime_to_timestamp($topic_time);
            }
            else
            {
                $last_post_time = datetime_to_timestamp($last_post_time);
            }
    
            if ( $last_post_time < $last_visit )
            {
                $image = $imgRepositoryWeb.'topic.gif';
                $alt='';
            }
            else
            {
                $image = $imgRepositoryWeb.'topic_hot.gif';
                $alt   = 'new post';
            }
    
            if($thisTopic['topic_status'] == 1) $image = $locked_image;
    
            echo '  <td><img src="' . $image . '" alt="' . $alt . '"></td>' . "\n";
    
            $topic_title = own_stripslashes($thisTopic['topic_title']);
            $topic_link  = 'viewtopic.php?forum=' . $forum_id . '&amp;topic='.$thisTopic['topic_id'];
    
            echo '  <td>'
                .'&nbsp;'
                .'<a href="' . $topic_link . '">' . $topic_title . '</a>&nbsp;&nbsp;';
    
            disp_mini_pager($topic_link, 'start', $replys+1, $posts_per_page);
    
            echo '</td>' . "\n"
                . '  <td align="center"><small>' . $replys . '</small></td>' . "\n"
                . '  <td align="center"><small>' . $thisTopic['prenom'] . ' ' . $thisTopic['nom'] . '<small></td>' . "\n"
                . '  <td align="center"><small>' . $thisTopic['topic_views'] . '<small></td>' . "\n";
    
            if ( !empty($last_post) )
            {
                echo '  <td align="center"><small>' . $last_post . '<small></td>' . "\n";
            }
            else
            {
                echo '  <td align="center"><small>' . $langNoPost . '<small></td>' . "\n";
            }
    
            echo ' </tr>' . "\n";
        }
    }
    
    echo '</table>' . "\n";
    
    $topicPager->disp_pager_tool_bar($pagerUrl);
}

/*-----------------------------------------------------------------
  Display Forum Footer
 -----------------------------------------------------------------*/

echo  '<br />
<center>
<small>Copyright &copy; 2000 - 2001 <a href="http://www.phpbb.com/" target="_blank">The phpBB Group</a></small>
</center>';

include($includePath.'/claro_init_footer.inc.php');

?>
