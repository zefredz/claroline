<?php // $Id$
/**
 * CLAROLINE
 *
 * Script view topic for forum tool
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
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

$tlabelReq = 'CLFRM';

require '../inc/claro_init_global.inc.php';

if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);

claro_set_display_mode_available(true);

/*-----------------------------------------------------------------
  Stats
 -----------------------------------------------------------------*/

event_access_tool($_tid, $_courseTool['label']);

/*-----------------------------------------------------------------
  Library
 -----------------------------------------------------------------*/

include_once $includePath . '/lib/forum.lib.php';
/*-----------------------------------------------------------------
  Initialise variables
 -----------------------------------------------------------------*/

$last_visit    = $_user['lastLogin'];
$error         = FALSE;
$allowed       = TRUE;
$error_message = '';

/*=================================================================
  Main Section
 =================================================================*/

// Get params

if ( isset($_REQUEST['topic']) ) $topic_id = (int) $_REQUEST['topic'];
else                             $topic_id = '';

if ( isset($_REQUEST['cmd']) )   $cmd = $_REQUEST['cmd'];
else                             $cmd = '';

if ( isset($_REQUEST['start'] ) ) $start = (int) $_REQUEST['start'];
else                              $start = 0;

$topicSettingList = get_topic_settings($topic_id);

$increaseTopicView = true;
if ($topicSettingList)
{
    $topic_subject    = $topicSettingList['topic_title' ];
    $lock_state       = $topicSettingList['topic_status'];
    $forum_id         = $topicSettingList['forum_id'    ];

    $forumSettingList   = get_forum_settings($forum_id);
    $forum_name         = $forumSettingList['forum_name'];
    $forum_cat_id       = $forumSettingList['cat_id'    ];
    $forum_post_allowed = ( $forumSettingList['forum_access'] != 0 ) ? true : false;

    /*
     * Check if the topic isn't attached to a group,  or -- if it is attached --,
     * check the user is allowed to see the current group forum.
     */

    if (   ! is_null($forumSettingList['idGroup'])
        && ! ( $forumSettingList['idGroup'] == $_gid || $is_groupAllowed) )
    {
        $allowed = FALSE;
        $error_message = get_lang('Not allowed');
    }
    else
    {
        // get post and use pager
        $postLister = new postLister($topic_id, $start, get_conf('posts_per_page'));
        $postList   = $postLister->get_post_list();
        $pagerUrl   = $_SERVER['PHP_SELF']."?topic=".$topic_id;

        // EMAIL NOTIFICATION COMMANDS
        // Execute notification preference change if the command was called

        if ( $cmd && isset($_uid) )
        {
            switch ($cmd)
            {
                case 'exNotify' :
                    request_topic_notification($topic_id, $_uid);
                    break;

                case 'exdoNotNotify' :
                    cancel_topic_notification($topic_id, $_uid);
                    break;
            }

            $increaseTopicView = false; // the notification change command doesn't
                                        // have to be considered as a new topic
                                        // consult
        }

        // Allow user to be have notification for this topic or disable it

        if ( isset($_uid) )  //anonymous user do not have this function
        {
            $notification_bloc = '<div style="float: right;">' . "\n"
                                . '<small>';

            if ( is_topic_notification_requested($topic_id, $_uid) )   // display link NOT to be notified
            {
                $notification_bloc .= '<img src="' . $imgRepositoryWeb . 'email.gif" alt="" />'
                                    . get_lang('Notify by email when replies are posted')
                                    . ' [<a href="' . $_SERVER['PHP_SELF'] . '?forum=' . $forum_id . '&amp;topic=' . $topic_id . '&amp;cmd=exdoNotNotify">'
                                    .get_lang('Disable')
                                    . '</a>]';
            }
            else   //display link to be notified for this topic
            {
                $notification_bloc .= '<a href="' . $_SERVER['PHP_SELF']
                                    . '?forum=' . $forum_id . '&amp;topic=' . $topic_id . '&amp;cmd=exNotify">'
                                    . '<img src="' . $imgRepositoryWeb . 'email.gif" alt="" /> '
                                    . get_lang('Notify by email when replies are posted')
                                    . '</a>';
            }

            $notification_bloc .= '</small>' . "\n"
                                . '</div>' . "\n";
        } //end not anonymous user
    }
}
else
{
    // forum or topic doesn't exist
    $allowed = false;
    $error_message = get_lang('Not allowed');
}

if ( $increaseTopicView ) increase_topic_view_count($topic_id); // else noop

/*=================================================================
  Display Section
 =================================================================*/
// Confirm javascript code

$htmlHeadXtra[] =
          "<script type=\"text/javascript\">
           function confirm_delete()
           {
               if (confirm('". clean_str_for_javascript(get_lang('Are you sure to delete')) . " ?'))
               {return true;}
               else
               {return false;}
           }
           </script>";

$interbredcrump[] = array ('url' => 'index.php', 'name' => get_lang('Forums'));
$noPHP_SELF       = true;

include $includePath . '/claro_init_header.inc.php';

if ( ! $allowed )
{
    echo claro_html_message_box($error_message);
}
else
{
    /*-----------------------------------------------------------------
      Display Forum Header
     -----------------------------------------------------------------*/

    $pagetype  = 'viewtopic';

    $is_allowedToEdit = claro_is_allowed_to_edit()
                        || ( $is_groupTutor && !$is_courseAdmin);

    echo claro_html_tool_title(get_lang('Forums'),
                          $is_allowedToEdit ? 'help_forum.php' : false);
    
    echo disp_forum_breadcrumb($pagetype, $forum_id, $forum_name, 0, $topic_subject);

    if ($forum_post_allowed)
    {
        echo disp_forum_toolbar($pagetype, $forum_id, $forum_cat_id, $topic_id);
    }

    $postLister->disp_pager_tool_bar($pagerUrl);

    echo '<table class="claroTable" width="100%">' . "\n"
    .    ' <tr align="left">' . "\n"
    .    '  <th class="superHeader">';

    // display notification link

    if ( !empty($notification_bloc) )
    {
        echo $notification_bloc;
    }

    echo $topic_subject
        . '  </th>' . "\n"
        . ' </tr>' . "\n";

    if (isset($_uid)) $date = $claro_notifier->get_notification_date($_uid);

    foreach ( $postList as $thisPost )
    {
        // Check if the forum post is after the last login
        // and choose the image according this state

        $post_time = datetime_to_timestamp($thisPost['post_time']);

        if (isset($_uid) && $claro_notifier->is_a_notified_ressource($_cid, $date, $_uid, $_gid, $_tid, $forum_id."-".$topic_id))
        $postImg = 'post_hot.gif';
        else
        $postImg = 'post.gif';

        echo ' <tr>' . "\n"

            .'  <th class="headerX">' . "\n"
            .'<img src="' . $imgRepositoryWeb . $postImg . '" alt="" />'
            . get_lang('Author') . ' : <b>' . $thisPost['firstname'] . ' ' . $thisPost['lastname'] . '</b> '
            .'<small>' . get_lang('Posted') . ' : ' . claro_disp_localised_date($dateTimeFormatLong, $post_time) . '</small>' . "\n"
            .'  </th>' . "\n"

            .' </tr>'. "\n"

            .' <tr>' . "\n"

            .'  <td>' . "\n"
            .claro_parse_user_text($thisPost['post_text']) . "\n";

        if ( $is_allowedToEdit )
        {
            echo '<p>' . "\n"

                . '<a href="editpost.php?post_id=' . $thisPost['post_id'] . '">'
                . '<img src="' . $imgRepositoryWeb . 'edit.gif" border="0" alt="' . get_lang('Edit') . '" />'
                . '</a>' . "\n"

                . '<a href="editpost.php?post_id=' . $thisPost['post_id'] . '&amp;delete=delete&amp;submit=submit" '
                . 'onClick="return confirm_delete();" >'
                . '<img src="' . $imgRepositoryWeb . 'delete.gif" border="0" alt="' . get_lang('Delete') . '" />'
                . '</a>' . "\n"

                . '</p>' . "\n";
        }

        echo    '  </td>' . "\n",
                ' </tr>' . "\n";

    } // end for each

    echo '</table>' . "\n";
    
    if ($forum_post_allowed)
    {
        $toolBar[] = '<a class="claroCmd" href="reply.php?topic=' . $topic_id . '&amp;forum=' . $forum_id . '&amp;gidReq='.$_gid.'">'
                   . '<img src="' . $imgRepositoryWeb . 'reply.gif" /> ' . get_lang('Reply') . '</a>' ."\n";
        echo claro_html_menu_horizontal($toolBar);
    }


    $postLister->disp_pager_tool_bar($pagerUrl);

}

/*-----------------------------------------------------------------
  Display Forum Footer
 -----------------------------------------------------------------*/

include($includePath.'/claro_init_footer.inc.php');

?>
