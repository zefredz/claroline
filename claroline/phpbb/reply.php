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
  Stats
 -----------------------------------------------------------------*/

event_access_tool(claro_get_current_tool_id(), claro_get_current_course_tool_data('label'));

/*-----------------------------------------------------------------
  Library
 -----------------------------------------------------------------*/

include_once get_path('incRepositorySys') . '/lib/forum.lib.php';
include_once get_path('incRepositorySys') . '/lib/pager.lib.php';

// for notification
include_once get_path('incRepositorySys') . '/lib/sendmail.lib.php';

$error = FALSE;
$error_message = '';
$allowed = TRUE;
$pagetype  = 'reply';

/*=================================================================
  Main Section
 =================================================================*/



if ( isset($_REQUEST['forum']) ) $forum_id = (int) $_REQUEST['forum'];
else                             $forum_id = 0;

if ( isset($_REQUEST['topic']) ) $topic_id = (int) $_REQUEST['topic'];
else                             $topic_id = 0;

if ( isset($_REQUEST['cancel']) )
{
    claro_redirect('viewtopic.php?topic=' . $topic_id . '&forum='.$forum_id);
    exit();
}

if ( isset($_REQUEST['message']) ) $message = $_REQUEST['message'];
else                               $message = '';

// XSS
$message = preg_replace( '/<script[^\>]*>|<\/script>|(onabort|onblur|onchange|onclick|ondbclick|onerror|onfocus|onkeydown|onkeypress|onkeyup|onload|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onreset|onresize|onselect|onsubmit|onunload)\s*=\s*"[^"]+"/i', '', $message );


$topicSettingList = get_topic_settings($topic_id);

if ( ! claro_is_user_authenticated() || ! claro_is_in_a_course()) claro_disp_auth_form(true);
elseif ( $topicSettingList )
{
    // Get forum and topics settings
    $forum_id         = $topicSettingList['forum_id'];
    $topic_title      = $topicSettingList['topic_title'];

    $forumSettingList = get_forum_settings($forum_id);

    $forum_name         = $forumSettingList['forum_name'  ];
    $forum_post_allowed = ( $forumSettingList['forum_access'] != 0 ) ? true : false;
    $forum_type         = $forumSettingList['forum_type'  ];
    $forum_groupId      = $forumSettingList['idGroup'     ];
    $forum_cat_id       = $forumSettingList['cat_id'      ];

    /**
     * Check if the topic isn't attached to a group,  or -- if it is attached --,
     * check the user is allowed to see the current group forum.
     */

    if ( ! $forum_post_allowed
        || ( ! is_null($forumSettingList['idGroup'])
            && ( !claro_is_in_a_group() || !claro_is_group_allowed() || $forumSettingList['idGroup'] != claro_get_current_group_id() ) ) )
    {
        // NOTE : $forumSettingList['idGroup'] != claro_get_current_group_id() is necessary to prevent any hacking
        // attempt like rewriting the request without $cidReq. If we are in group
        // forum and the group of the concerned forum isn't the same as the session
        // one, something weird is happening, indeed ...
        $allowed = FALSE;
        $error_message = get_lang('Not allowed') ;
    }

    if ( isset($_REQUEST['submit']) )
    {
        if ( trim(strip_tags($message)) != '' )
        {

            if ( get_conf('allow_html') == 0 || isset($html) ) $message = htmlspecialchars($message);

            $lastName   = claro_get_current_user_data('lastName');
            $firstName  = claro_get_current_user_data('firstName');
            $poster_ip  = $_SERVER['REMOTE_ADDR'];
            $time       = date('Y-m-d H:i');

            create_new_post($topic_id, $forum_id, claro_get_current_user_id(), $time, $poster_ip, $lastName, $firstName, $message);

            // notify eventmanager that a new message has been posted

            $eventNotifier->notifyCourseEvent("forum_answer_topic",claro_get_current_course_id(), claro_get_current_tool_id(), $forum_id."-".$topic_id, claro_get_current_group_id(), "0");

            trig_topic_notification($topic_id);
        }
        else
        {
            $error = TRUE;
            $error_message = get_lang('You cannot post an empty message');
        }
    }
}
else
{
    // topic doesn't exist
    $error = 1;
    $error_message = get_lang('Not allowed');
}

/*=================================================================
  Display Section
 =================================================================*/

$interbredcrump[] = array ('url' => 'index.php', 'name' => get_lang('Forums'));
$noPHP_SELF       = true;

include get_path('incRepositorySys') . '/claro_init_header.inc.php';

$pagetype  = 'reply';

$is_allowedToEdit = claro_is_allowed_to_edit();

echo claro_html_tool_title(get_lang('Forums'),
                      $is_allowedToEdit ? 'help_forum.php' : false);

if ( !$allowed )
{
    // not allowed
    echo claro_html_message_box($error_message);
}
else
{

    if ( isset($_REQUEST['submit']) && !$error )
    {
        // DISPLAY SUCCES MESSAGE
        disp_confirmation_message (get_lang('Your message has been entered'), $forum_id, $topic_id);
    }
    else
    {
        if ( $error )
        {
            echo claro_html_message_box($error_message);
        }

        echo claro_html_menu_horizontal(disp_forum_toolbar($pagetype, $forum_id, 0, $topic_id));

        echo disp_forum_breadcrumb($pagetype, $forum_id, $forum_name, $topic_id, $topic_title);

        echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n"
            . '<input type="hidden" name="forum" value="' . $forum_id . '" />' . "\n"
            . '<input type="hidden" name="topic" value="' . $topic_id . '" />' . "\n";

        echo '<table border="0" width="100%">' . "\n"
            . '<tr valign="top">' . "\n"
            . '<td align="right"><br />' . get_lang('Message body') . '&nbsp;:</td>'
            . '<td>'
            .claro_html_textarea_editor('message', $message)
            .'</td>'
            . '</tr>'
            . '<tr valign="top"><td>&nbsp;</td>'
            . '<td>'
            . '<input type="submit" name="submit" value="' . get_lang('Ok') . '" />&nbsp; '
            . '<input type="submit" name="cancel" value="' . get_lang('Cancel') . '" />'
            . '</tr>'
            . '</table>'
            . '</form>' ;

        echo '<p align="center"><a href="viewtopic.php?topic=' . $topic_id . '&forum=' . $forum_id . '" target="_blank">' . get_lang('Topic review') . '</a>';

    } // end else if submit
}

// Display Forum Footer

include(get_path('incRepositorySys').'/claro_init_footer.inc.php');
?>
