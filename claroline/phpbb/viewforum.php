<?php // $Id$
/**
 * CLAROLINE
 *
 * Script displays topics list of a forum
 *
 * @version 1.9 $Revision$
 *
 * @copyright 2001-2008 Universite catholique de Louvain (UCL)
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
$toolList= array();

require '../inc/claro_init_global.inc.php';

if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);
$currentContext = ( claro_is_in_a_group() ) ? CLARO_CONTEXT_GROUP : CLARO_CONTEXT_COURSE;

claro_set_display_mode_available(true);

/*-----------------------------------------------------------------
  Library
 -----------------------------------------------------------------*/

include_once get_path('incRepositorySys') . '/lib/pager.lib.php';
include_once get_path('incRepositorySys') . '/lib/forum.lib.php';

/*-----------------------------------------------------------------
  Initialise variables
 -----------------------------------------------------------------*/

$last_visit    = claro_get_current_user_data('lastLogin');
$error         = false;
$forumAllowed  = true;
$dialogBox = new DialogBox();

/*=================================================================
  Main Section
 =================================================================*/

// Get params

if ( isset($_REQUEST['forum']) ) $forum_id = (int) $_REQUEST['forum'];
else                             $forum_id = 0;

if ( isset($_REQUEST['cmd']) )   $cmd = $_REQUEST['cmd'];
else                             $cmd = '';

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

    if ( ! is_null($forumSettingList['idGroup'])
        && ( !claro_is_in_a_group() || !claro_is_group_allowed() || $forumSettingList['idGroup'] != claro_get_current_group_id() ) )
    {
        // user are not allowed to see topics of this group
        $forumAllowed       = false;
        $dialogBox->error( get_lang('Not allowed') );
    }

    if ( $forumAllowed )
    {
        // Get topics list

        $topicLister = new topicLister($forum_id, $start, get_conf('topics_per_page') );
        $topicList   = $topicLister->get_topic_list();
        $pagerUrl = htmlspecialchars(Url::Contextualize( get_module_url('CLFRM') . '/viewforum.php?forum=' . $forum_id ) );
    }
}
else
{
    // No forum
    $forumAllowed       = false;
    $forum_post_allowed = false;
    $forum_cat_id       = null;
    $dialogBox->error( get_lang('Not allowed') );
}

/*=================================================================
  Display Section
 =================================================================*/

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Forums'), 'index.php' );
$noPHP_SELF       = true;


    // Show Group tools
    // only if in group forum.

    if ( $currentContext == CLARO_CONTEXT_GROUP )
    {
        $groupToolList = forum_group_tool_list(claro_get_current_group_id());
    }

include get_path('incRepositorySys') . '/claro_init_header.inc.php';

if ( ! $forumAllowed )
{
    echo $dialogBox->render();
}
else
{
    if ( $cmd && claro_is_user_authenticated() && claro_is_course_manager() )
    {
        switch ($cmd)
        {
            case 'exNotify' :
                request_forum_notification($forum_id, claro_get_current_user_id());
                break;

            case 'exdoNotNotify' :
                cancel_forum_notification($forum_id, claro_get_current_user_id());
                break;
        }
    }
    
    // Allow user to be have notification for this topic or disable it

    if ( claro_is_user_authenticated() && claro_is_course_manager() )  //anonymous user do not have this function
    {
        $notification_bloc = '<span style="float: right;" class="claroCmd">';

        if ( is_forum_notification_requested($forum_id, claro_get_current_user_id()) )   // display link NOT to be notified
        {
            $notification_url = Url::Contextualize(
                $_SERVER['PHP_SELF']
                . '?forum=' . $forum_id . '&amp;cmd=exdoNotNotify'
            );
            
            $notification_bloc .= '<img src="' . get_icon_url('mail_close') . '" alt="" style="vertical-align: text-bottom" />';
            $notification_bloc .= get_lang('Notify by email when topics are created');
            $notification_bloc .= ' [<a href="' .htmlspecialchars($notification_url). '">';
            $notification_bloc .= get_lang('Disable');
            $notification_bloc .= '</a>]';
        }
        else   //display link to be notified for this topic
        {
            $notification_url = Url::Contextualize(
                $_SERVER['PHP_SELF']
                . '?forum=' . $forum_id . '&amp;cmd=exNotify'
            );
            
            $notification_bloc .= '<a href="' . htmlspecialchars($notification_url). '">';
            $notification_bloc .= '<img src="' . get_icon_url('mail_close') . '" alt="" /> ';
            $notification_bloc .= get_lang('Notify by email when topics are created');
            $notification_bloc .= '</a>';
        }

        $notification_bloc .= '</span>' . "\n";
    } //end not anonymous user
    
    /*-----------------------------------------------------------------
      Display Forum Header
    -----------------------------------------------------------------*/

    $pagetype = 'viewforum';

    $is_allowedToEdit = claro_is_allowed_to_edit()
                        || (  claro_is_group_tutor() && !claro_is_course_manager());
                        // (  claro_is_group_tutor()
                        //  is added to give admin status to tutor
                        // && !claro_is_course_manager())
                        // is added  to let course admin, tutor of current group, use student mode

    echo claro_html_tool_title(get_lang('Forums'),
                          $is_allowedToEdit ? 'help_forum.php' : false);

    echo disp_forum_breadcrumb($pagetype, $forum_id, $forum_name);


    if ( isset($groupToolList) )
    {
        echo '<p>' . claro_html_menu_horizontal($groupToolList) .'</p>';

    }

    if ($forum_post_allowed)
    {
        echo '<p>' . claro_html_menu_horizontal(disp_forum_toolbar($pagetype, $forum_id, $forum_cat_id, 0)) . '</p>';
    }

    $topicLister->disp_pager_tool_bar($pagerUrl);

    echo '<table class="claroTable emphaseLine" width="100%">' . "\n"

        .' <tr class="superHeader">'                  . "\n"
        .'  <th colspan="6">'
        . ( !empty($notification_bloc) ? $notification_bloc . "\n" : '' )
        . $forum_name
        . '</th>' . "\n"
        .' </tr>'                                     . "\n"

        .' <tr class="headerX" align="left">'                            . "\n"
        .'  <th>&nbsp;' . get_lang('Topic') . '</th>'                             . "\n"
        .'  <th width="9%"  align="center">' . get_lang('Posts') . '</th>'        . "\n"
        .'  <th width="20%" align="center">&nbsp;' . get_lang('Author') . '</th>' . "\n"
        .'  <th width="8%"  align="center">' . get_lang('Seen') . '</th>'       . "\n"
        .'  <th width="15%" align="center">' . get_lang('Last message') . '</th>'    . "\n"
        .' </tr>' . "\n";

    $topics_start = $start;

    if ( count($topicList) == 0 )
    {
        echo '<tr>' . "\n"
        .    '<td colspan="5" align="center">'
        .    get_lang('There are no topics for this forum. You can post one')
        .    '</td>'. "\n"
        .    '</tr>' . "\n"
        ;
    }
    else
    {
        if (claro_is_user_authenticated()) $date = $claro_notifier->get_notification_date(claro_get_current_user_id());

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

            if ( claro_is_user_authenticated() && $claro_notifier->is_a_notified_ressource(claro_get_current_course_id(), $date, claro_get_current_user_id(), claro_get_current_group_id(), claro_get_current_tool_id(), $forum_id."-".$thisTopic['topic_id'],FALSE))
            {
                $class = 'item hot';
            }
            else
            {
                $class = 'item';
            }

            echo '<td>'
            .    '<span class="'.$class.'">'
            .    '<img src="' . get_icon_url('topic') . '" alt="" />'
            ;

            $topic_title = $thisTopic['topic_title'];
            $topic_link  = htmlspecialchars(Url::Contextualize( get_module_url('CLFRM') . '/viewtopic.php?topic='.$thisTopic['topic_id']
                        .  (is_null($forumSettingList['idGroup']) ?
                           '' : '&amp;gidReq ='.$forumSettingList['idGroup']) ));

            echo '&nbsp;'
            .    '<a href="' . $topic_link . '">' . $topic_title . '</a>'
            .    '</span>'
            .    '&nbsp;&nbsp;'
            ;

            disp_mini_pager($topic_link, 'start', $replys, get_conf('posts_per_page') );

            echo '</td>' . "\n"
                .'<td align="center"><small>' . $replys . '</small></td>' . "\n"
                .'<td align="center"><small>' . $thisTopic['prenom'] . ' ' . $thisTopic['nom'] . '</small></td>' . "\n"
                .'<td align="center"><small>' . $thisTopic['topic_views'] . '</small></td>' . "\n";

            if ( !empty($last_post) )
            {
                echo  '<td align="center">'
                    . '<small>'
                    . claro_html_localised_date(get_locale('dateTimeFormatShort'), $last_post)
                    . '</small>'
                    . '</td>' . "\n";
            }
            else
            {
                echo '<td align="center"><small>' . get_lang('No post') . '</small></td>' . "\n";
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

include(get_path('incRepositorySys').'/claro_init_footer.inc.php');
?>