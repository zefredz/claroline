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
    
    if( claro_is_allowed_to_edit() )
    {
        $out .= '<div style="float: right;">' . "\n"
        .   '<img src=' . get_icon_url('html') . '" alt="" /> <a href="' . htmlspecialchars( Url::Contextualize( 'export.php?type=HTML&topic=' . $topic_id )) . '" target="_blank">' . get_lang( 'Export to HTML' ) . '</a>' . "\n"
        .   '<img src="'. get_icon_url('mime/pdf') . '" alt="" /> <a href="' . htmlspecialchars( Url::Contextualize( 'export.php?type=PDF&topic=' . $topic_id ) ) . '" target="_blank">' . get_lang( 'Export to PDF' ) .'</a>' . "\n"
        .   '</div>'
        ;
    }

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
    
    $form = new PhpTemplate( get_path( 'incRepositorySys' ) . '/templates/forum_viewtopic.tpl.php' );
    
    $form->assign( 'forum_id', $forum_id );
    $form->assign( 'topic_id', $topic_id );
    $form->assign( 'topic_subject', $topic_subject );
    $form->assign( 'postList', $postList );
    $form->assign( 'is_allowedToEdit', $is_allowedToEdit );
    
    if (claro_is_user_authenticated())
    {
        $date = $claro_notifier->get_notification_date(claro_get_current_user_id());
    }
    else
    {
        $date = null;
    }
    
    $form->assign( 'date', $date );
    $form->assign( 'is_a_notified_ressource', $claro_notifier->is_a_notified_ressource(claro_get_current_course_id(), $date, claro_get_current_user_id(), claro_get_current_group_id(), claro_get_current_tool_id(), $forum_id."-".$topic_id ) );
    $out .= $form->render();

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