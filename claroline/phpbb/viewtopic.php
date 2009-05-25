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

if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);

claro_set_display_mode_available(true);

/*-----------------------------------------------------------------
Library
-----------------------------------------------------------------*/

include_once get_path('incRepositorySys') . '/lib/forum.lib.php';
include_once get_path('incRepositorySys') . '/lib/user.lib.php';
/*-----------------------------------------------------------------
Initialise variables
-----------------------------------------------------------------*/

$last_visit    = claro_get_current_user_data('lastLogin');
$error         = FALSE;
$allowed       = TRUE;
$dialogBox = new DialogBox();

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
    $lastPostId         = $topicSettingList['topic_last_post_id'];

    /*
    * Check if the topic isn't attached to a group,  or -- if it is attached --,
    * check the user is allowed to see the current group forum.
    */

    if (   ! is_null($forumSettingList['idGroup'])
    && ! ( ($forumSettingList['idGroup'] == claro_get_current_group_id()) || claro_is_group_allowed()) )
    {
        $allowed = FALSE;
        $dialogBox->error( get_lang('Not allowed') );
    }
    else
    {
        // get post and use pager
        $postLister = new postLister($topic_id, $start, get_conf('posts_per_page'));
        $postList   = $postLister->get_post_list();
        $totalPosts = $postLister->sqlPager->get_total_item_count();
        $pagerUrl   = htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?topic=' . $topic_id ));

        // EMAIL NOTIFICATION COMMANDS
        // Execute notification preference change if the command was called

        if ( $cmd && claro_is_user_authenticated() )
        {
            switch ($cmd)
            {
                case 'exNotify' :
                    request_topic_notification($topic_id, claro_get_current_user_id());
                    break;

                case 'exdoNotNotify' :
                    cancel_topic_notification($topic_id, claro_get_current_user_id());
                    break;
            }

            $increaseTopicView = false; // the notification change command doesn't
            // have to be considered as a new topic
            // consult
        }

        // Allow user to be have notification for this topic or disable it

        if ( claro_is_user_authenticated() )  //anonymous user do not have this function
        {
            $notification_bloc = '<span style="float: right;" class="claroCmd">';

            if ( is_topic_notification_requested($topic_id, claro_get_current_user_id()) )   // display link NOT to be notified
            {
                $notification_url = Url::Contextualize(
                    $_SERVER['PHP_SELF']
                    . '?forum=' . $forum_id . '&amp;topic='
                    . $topic_id . '&amp;cmd=exdoNotNotify'
                );
                
                $notification_bloc .= '<img src="' . get_icon_url('mail_close') . '" alt="" style="vertical-align: text-bottom" />';
                $notification_bloc .= get_lang('Notify by email when replies are posted');
                $notification_bloc .= ' [<a href="' .htmlspecialchars($notification_url). '">';
                $notification_bloc .= get_lang('Disable');
                $notification_bloc .= '</a>]';
            }
            else   //display link to be notified for this topic
            {
                $notification_url = Url::Contextualize(
                    $_SERVER['PHP_SELF']
                    . '?forum=' . $forum_id . '&amp;topic='
                    . $topic_id . '&amp;cmd=exNotify'
                );
                
                $notification_bloc .= '<a href="' . htmlspecialchars($notification_url). '">';
                $notification_bloc .= '<img src="' . get_icon_url('mail_close') . '" alt="" /> ';
                $notification_bloc .= get_lang('Notify by email when replies are posted');
                $notification_bloc .= '</a>';
            }

            $notification_bloc .= '</span>' . "\n";
        } //end not anonymous user
    }
}
else
{
    // forum or topic doesn't exist
    $allowed = false;
    $dialogBox->error( get_lang('Not allowed') );
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

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Forums'), 'index.php' );
$noPHP_SELF       = true;

CssLoader::getInstance()->load( 'clfrm', 'screen');

$out = '';

if ( ! $allowed )
{
    $out .= $dialogBox->render();
}
else
{
    /*-----------------------------------------------------------------
    Display Forum Header
    -----------------------------------------------------------------*/

    $pagetype  = 'viewtopic';

    $is_allowedToEdit = claro_is_allowed_to_edit()
    || ( claro_is_group_tutor() && !claro_is_course_manager());

    $out .= claro_html_tool_title(get_lang('Forums'),
    $is_allowedToEdit ? 'help_forum.php' : false);

    $out .= disp_forum_breadcrumb($pagetype, $forum_id, $forum_name, 0, $topic_subject);

    if ($forum_post_allowed)
    {
        $toolList = disp_forum_toolbar($pagetype, $forum_id, $forum_cat_id, $topic_id);
        
        if ( count($postList) > 2 ) // if less than 2 las message is visible
        {
            $start_last_message = ( ceil($totalPosts / get_conf('posts_per_page')) -1 ) * get_conf('posts_per_page') ;

            $lastMsgUrl = get_module_url('CLFRM')
            .             '/viewtopic.php?forum=' . $forum_id
            .             '&amp;topic=' . $topic_id
            .             '&amp;start=' . $start_last_message
            .             '#post' . $lastPostId
            ;
            
            $toolList[] = claro_html_cmd_link(htmlspecialchars(Url::Contextualize($lastMsgUrl)),get_lang('Last message'));
        }
        
        $out .= claro_html_menu_horizontal($toolList);
    }

    $out .= $postLister->disp_pager_tool_bar($pagerUrl);

    $out .= '<h4 class="header">' . "\n";

    // display notification link

    if ( !empty($notification_bloc) )
    {
        $out .= $notification_bloc . "\n";
    }

    $out .= $topic_subject . "\n"
    .    '</h4>' . "\n"
    ;

    if (claro_is_user_authenticated())
    {
        $date = $claro_notifier->get_notification_date(claro_get_current_user_id());
    }

    foreach ( $postList as $thisPost )
    {
        // notify if is new message
        $post_time = datetime_to_timestamp($thisPost['post_time']);

        if (claro_is_user_authenticated()
            && $claro_notifier->is_a_notified_ressource(claro_get_current_course_id(), $date, claro_get_current_user_id(), claro_get_current_group_id(), claro_get_current_tool_id(), $forum_id."-".$topic_id))
        {
            $class = 'item hot';
        }
        else
        {
            $class = 'item';
        }
        
        // get user picture
        $userData = user_get_properties( $thisPost['poster_id'] );

        $picturePath = user_get_picture_path( $userData );

        if ( $picturePath && file_exists( $picturePath ) )
        {
            $pictureUrl = user_get_picture_url( $userData );
        }
        else
        {
            $pictureUrl = null;
        }
        
        $out .= '<div id="post'. $thisPost['post_id'] .'" class="threadPost">'
        .    '<div class="threadPostInfo">'
        .    ( !is_null($pictureUrl) ?'<div class="threadPosterPicture"><img src="' . $pictureUrl . '" alt=" " /></div>':'' ) . "\n"
        .    '<b>' . $thisPost['firstname'] . ' ' . $thisPost['lastname'] . '</b> '
        .    '<br />'
        .    '<small>' . claro_html_localised_date(get_locale('dateTimeFormatLong'), $post_time) . '</small>' . "\n"
        ;

        
        $out .= '  </div>' . "\n"

        .    '<div class="threadPostContent">' . "\n"
        .    '<span class="threadPostIcon '.$class.'"><img src="' . get_icon_url( 'post' ) . '" alt="" /></span><br />' . "\n"
        .    claro_parse_user_text($thisPost['post_text']) . "\n";

        if ( $is_allowedToEdit )
        {
            $out .= '<p>' . "\n"

            . '<a href="'.htmlspecialchars(Url::Contextualize( get_module_url('CLFRM') . '/editpost.php?post_id=' . $thisPost['post_id'] )) . '">'
            . '<img src="' . get_icon_url('edit') . '" alt="' . get_lang('Edit') . '" />'
            . '</a>' . "\n"

            . '<a href="'.htmlspecialchars(Url::Contextualize( get_module_url('CLFRM') . '/editpost.php?post_id=' . $thisPost['post_id'] . '&amp;delete=delete&amp;submit=submit')).'" '
            . 'onclick="return confirm_delete();" >'
            . '<img src="' . get_icon_url('delete') . '" alt="' . get_lang('Delete') . '" />'
            . '</a>' . "\n"

            . '</p>' . "\n";
        }

        $out .= '</div>' . "\n"
        .    '<div class="spacer"></div>' . "\n\n"
        .    '</div>' . "\n"
        ;

    } // end for each

    if ($forum_post_allowed)
    {
        $replyUrl = Url::Contextualize( get_module_url('CLFRM')
            . '/reply.php'
            . '?topic=' . $topic_id
            . '&amp;forum=' . $forum_id
        );
            
        $toolBar[] = claro_html_cmd_link( htmlspecialchars( $replyUrl )
                                        , '<img src="' . get_icon_url('reply') . '" alt="" />'
                                        . ' '
                                        . get_lang('Reply')
                                        );
        $out .= claro_html_menu_horizontal($toolBar);
    }


    $out .= $postLister->disp_pager_tool_bar($pagerUrl);

}

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>