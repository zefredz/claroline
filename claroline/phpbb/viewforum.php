<?php // $Id$
/**
 * CLAROLINE
 *
 * Script displays topics list of a forum
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

if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);

claro_set_display_mode_available(true);

/*-----------------------------------------------------------------
  Stats
 -----------------------------------------------------------------*/

event_access_tool($_tid, $_courseTool['label']);

/*-----------------------------------------------------------------
  Library
 -----------------------------------------------------------------*/

require $includePath . '/lib/pager.lib.php';
include $includePath . '/lib/forum.lib.php';

/*-----------------------------------------------------------------
  Initialise variables
 -----------------------------------------------------------------*/

$last_visit    = $_user['lastLogin'];
$error         = false;
$forumAllowed  = true;
$error_message = '';

/*=================================================================
  Main Section
 =================================================================*/

// Get params

if ( isset($_REQUEST['forum']) ) $forum_id = (int) $_REQUEST['forum'];
else                             $forum_id = 0;

if ( !empty($_REQUEST['start']) ) $start = (int) $_REQUEST['start'];
else                              $start = 0;

// Get forum settings
$forumSettingList = get_forum_settings($forum_id);

if ( $forumSettingList )
{
    $forum_name         = $forumSettingList['forum_name'];
    $forum_cat_id       = $forumSettingList['cat_id'    ];
    $forum_post_allowed = ( $forumSettingList['forum_access'] != 0 ) ? true : false;

    /* 
     * Check if the forum isn't attached to a group,  or -- if it is attached --, 
     * check the user is allowed to see the current group forum.
     */

    if (   ! is_null($forumSettingList['idGroup']) 
        && ( $forumSettingList['idGroup'] != $_gid || ! $is_groupAllowed) )
    {
        // user are not allowed to see topics of this group
        $forumAllowed       = false;
        $error_message = $langNotAllowed;
    }

    if ( $forumAllowed )
    {  
        // Get topics list

        $topicLister = new topicLister($forum_id, $start, $topics_per_page);
        $topicList   = $topicLister->get_topic_list();
        $pagerUrl = 'viewforum.php?forum=' . $forum_id . '&gidReq='.$_gid;
    }
}
else
{
    // No forum
    $forumAllowed       = false;
    $forum_post_allowed = false;
    $$forum_cat_id      = null;
    $error_message      = $langNotAllowed;
}

/*=================================================================
  Display Section
 =================================================================*/
 
if (     $forum_cat_id == GROUP_FORUMS_CATEGORY
     && ($is_groupMember || $is_groupTutor || $is_courseAdmin ) )
{
    $interbredcrump[]  = array ('url'=>'../group/group.php'      , 'name'=> $langGroups);
    $interbredcrump[]  = array ('url'=>'../group/group_space.php', 'name'=> $_group['name']);
}

include $includePath . '/claro_init_header.inc.php';

if ( ! $forumAllowed )
{
    echo claro_disp_message_box($error_message);
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

    echo claro_disp_tool_title($langForums, 
                          $is_allowedToEdit ? 'help_forum.php' : false);

    // Show Group Documents and Group Space
    // only if in Category 2 = Group Forums Category
    
    if (    $forum_cat_id == GROUP_FORUMS_CATEGORY 
        && ($is_groupMember || $is_allowedToEdit ) ) disp_forum_group_toolbar($_gid);

    if ($forum_post_allowed) disp_forum_toolbar($pagetype, $forum_id, $forum_cat_id, 0);

    disp_forum_breadcrumb($pagetype, $forum_id, $forum_name);
    
    $topicLister->disp_pager_tool_bar($pagerUrl);
    
    echo '<table class="claroTable emphaseLine" width="100%">' . "\n"
    
        .' <tr class="superHeader">'                  . "\n"
        .'  <th colspan="6">' . $forum_name . '</th>' . "\n"
        .' </tr>'                                     . "\n"
    
        .' <tr class="headerX" align="left">'                            . "\n"
        .'  <th>&nbsp;' . $l_topic . '</th>'                             . "\n"
        .'  <th width="9%"  align="center">' . $l_posts . '</th>'        . "\n"
        .'  <th width="20%" align="center">&nbsp;' . $l_poster . '</th>' . "\n"
        .'  <th width="8%"  align="center">' . $langSeen . '</th>'       . "\n"
        .'  <th width="15%" align="center">' . $langLastMsg . '</th>'    . "\n"
        .' </tr>' . "\n";
    
    $topics_start = $start;
    
    if ( count($topicList) == 0 )
    {
        echo ' <tr>' . "\n" 
            .'  <td colspan="5" align="center">' . $l_notopics . '</td>'. "\n"
            .' </tr>' . "\n";
    }
    else 
    {
        if (isset($_uid)) $date = $claro_notifier->get_notification_date($_uid);
        
        foreach ( $topicList as $thisTopic )
        {
            echo ' <tr>' . "\n";
    
            $replys         = $thisTopic['topic_replies'];
            $topic_time     = $thisTopic['topic_time'   ];
            $last_post_time = datetime_to_timestamp( $thisTopic['post_time']);
            $last_post      = datetime_to_timestamp( $thisTopic['post_time'] );
    
            if ( empty($last_post_time) )
            {
                $last_post_time = datetime_to_timestamp($topic_time);
            }
    
            if (isset($_uid) && $claro_notifier->is_a_notified_ressource($_cid, $date, $_uid, $_gid, $_tid, $forum_id."-".$thisTopic['topic_id'],FALSE))
            {
                $image = $imgRepositoryWeb.'topic_hot.gif';
                $alt='';
            }
            else
            {
                $image = $imgRepositoryWeb.'topic.gif';
                $alt   = 'new post';
            }
    
            if($thisTopic['topic_status'] == 1) $image = $locked_image;
    
            echo '<td>'
                .'<img src="' . $image . '" alt="' . $alt . '" />';
    
            $topic_title = $thisTopic['topic_title'];
            $topic_link  = 'viewtopic.php?topic='.$thisTopic['topic_id']
                        .  (is_null($forumSettingList['idGroup']) ? 
                           '' : '&amp;gidReq ='.$forumSettingList['idGroup']);
    
            echo '&nbsp;'
                .'<a href="' . $topic_link . '">' . $topic_title . '</a>&nbsp;&nbsp;';
    
            disp_mini_pager($topic_link, 'start', $replys+1, $posts_per_page);
    
            echo '</td>' . "\n"
                .'<td align="center"><small>' . $replys . '</small></td>' . "\n"
                .'<td align="center"><small>' . $thisTopic['prenom'] . ' ' . $thisTopic['nom'] . '<small></td>' . "\n"
                .'<td align="center"><small>' . $thisTopic['topic_views'] . '<small></td>' . "\n";
    
            if ( !empty($last_post) )
            {
                echo  '<td align="center">'
                    . '<small>' 
                    . claro_disp_localised_date($dateTimeFormatShort, $last_post)
                    . '<small>'
                    . '</td>' . "\n";
            }
            else
            {
                echo '  <td align="center"><small>' . $langNoPost . '<small></td>' . "\n";
            }
    
            echo ' </tr>' . "\n";
        }
    }
    
    echo '</table>' . "\n";
    
    $topicLister->disp_pager_tool_bar($pagerUrl);
}

/*-----------------------------------------------------------------
  Display Forum Footer
 -----------------------------------------------------------------*/

include($includePath.'/claro_init_footer.inc.php');

?>
