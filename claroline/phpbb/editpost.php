<?php  // $Id$
/**
 * CLAROLINE
 *
 * Script for forum tool
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

// initialise variables

$last_visit = claro_get_current_user_data('lastLogin');
$error = FALSE;
$allowed = TRUE;
$dialogBox = new DialogBox();

$pagetype  = 'editpost';

/*=================================================================
  Main Section
 =================================================================*/

if ( isset($_REQUEST['post_id']) ) $post_id = (int) $_REQUEST['post_id'];
else                               $post_id = 0;

$is_allowedToEdit = claro_is_allowed_to_edit()
                    || ( claro_is_group_tutor() && !claro_is_course_manager());
                    // ( claro_is_group_tutor()
                    //  is added to give admin status to tutor
                    // && !claro_is_course_manager())
                    // is added  to let course admin, tutor of current group, use student mode

$postSettingList =  get_post_settings($post_id);

if ( $postSettingList && $is_allowedToEdit )
{
    $topic_id        = $postSettingList['topic_id'];

    $forumSettingList = get_forum_settings($postSettingList['forum_id']);

    $forum_name         = stripslashes($forumSettingList['forum_name']);
    $forum_cat_id       = $forumSettingList['cat_id'      ];

    /*
     * Check if the topic isn't attached to a group,  or -- if it is attached --,
     * check the user is allowed to see the current group forum.
     */

    if (   ! is_null($forumSettingList['idGroup'])
        && ( ($forumSettingList['idGroup'] != claro_get_current_group_id()) || ! claro_is_group_allowed()) )
    {
        // NOTE : $forumSettingList['idGroup'] != claro_get_current_group_id() is necessary to prevent any hacking
        // attempt like rewriting the request without $cidReq. If we are in group
        // forum and the group of the concerned forum isn't the same as the session
        // one, something weird is happening, indeed ...
        $allowed = false;
        $dialogBox->error( get_lang('Not allowed') );
    }
    else
    {
        if ( isset($_REQUEST['cancel']) )
        {
            claro_redirect('viewtopic.php?topic=' . $topic_id );
            exit();
        }

        if ( isset($_REQUEST['submit']) )
        {
            /*-----------------------------------------------------------------
              Edit Post
             -----------------------------------------------------------------*/

            if ( ! $postSettingList ) error_die($err_db_retrieve_data);

            $poster_id        = $postSettingList['poster_id'];
            $forum_id         = $postSettingList['forum_id' ];
            $topic_id         = $postSettingList['topic_id' ];
            $this_post_time   = $postSettingList['post_time'];
            list($day, $time) = explode(' ', $postSettingList['post_time']);
            $date             = date('Y-m-d H:i');

            if ( isset($_REQUEST['message']) )
            {
                $message = $_REQUEST['message'];

                // XSS
                $message = preg_replace( '/<script[^\>]*>|<\/script>|(onabort|onblur|onchange|onclick|ondbclick|onerror|onfocus|onkeydown|onkeypress|onkeyup|onload|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onreset|onresize|onselect|onsubmit|onunload)\s*=\s*"[^"]+"/i', '', $message );
            }
            else
            {
                $message = '';
            }

            if ( isset($_REQUEST['subject']) )
            {
                $subject = $_REQUEST['subject'];
            }
            else
            {
                $subject = '';
            }

            if ( !isset($_REQUEST['delete']) )
            {
                update_post($post_id, $topic_id, $message, $subject);
            }
            else
            {
                delete_post($post_id, $topic_id, $forum_id, $poster_id);
            }

        } // end submit management
        else
        {
            /*==========================
                  EDIT FORM BUILDING
              ==========================*/
            $postSettingList  = get_post_settings($post_id);

            list($day, $time) = explode(' ', $postSettingList['post_time']);
            $message = $postSettingList['post_text'];
            $message = preg_replace('#</textarea>#si', '&lt;/TEXTAREA&gt;', $message);
                // Special handling for </textarea> tags in the message,
                // which can break the editing form.

            $forum_id = $postSettingList['forum_id' ];
            $topic_id = $postSettingList['topic_id'];

            $topicSettingList = get_topic_settings($topic_id);
            $subject          = $topicSettingList['topic_title'];
        }
    }
}
else
{
    // post doesn't exist or not allowed to edit post
    $allowed = FALSE;
    $dialogBox->error( get_lang('Not allowed') );
}

/*=================================================================
  Display Section
 =================================================================*/

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Forums'), 'index.php' );
$noPHP_SELF       = true;

$out = '';

// Forum Title

$out .= claro_html_tool_title(get_lang('Forums'), $is_allowedToEdit ? 'help_forum.php' : false);

if ( !$allowed || !$is_allowedToEdit )
{
      $out .= $dialogBox->render();
}
else
{

    if ( isset($_REQUEST['submit']) && !$error)
    {
        if ( ! isset($_REQUEST['delete']) )
        {
            $out .= disp_confirmation_message (get_lang('Your message has been entered'), $forum_id, $topic_id);
        }
        else
        {
            $out .= disp_confirmation_message (get_lang('Your message has been deleted'), $forum_id);
        }
    }
    else
    {
        $first_post = is_first_post($topic_id, $post_id);

        if ( $error )
        {
            $out .= $dialogBox->render();
        }

        $out .= disp_forum_breadcrumb($pagetype, $forum_id, $forum_name, $topic_id, $subject)
        .    claro_html_menu_horizontal(disp_forum_toolbar($pagetype, $forum_id, $topic_id, 0))

        .    '<form action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" method="post" >' . "\n"
        .    claro_form_relay_context()
        .    '<input type="hidden" name="post_id" value="' . $post_id . '" />' . "\n"
        .    '<table border="0" width="100%" >' . "\n"
        ;

        if ( $first_post )
        {
            $out .= '<tr valign="top">' . "\n"
            .    '<td align="right">' . "\n"
            .    '<label for="subject">' . get_lang('Subject') . '</label> : '
            .    '</td>' . "\n"
            .    '<td>' . "\n"
            .    '<input type="text" name="subject" id="subject" size="50" maxlength="100" value="' . htmlspecialchars($subject) . '" />'
            .    '</td>' . "\n"
            .    '</tr>' . "\n"
            ;
        }

        $out .= '<tr valign="top">' . "\n"
        .    '<td align="right">' . "\n"
        .    '<br />' . get_lang('Message body') . ' : ' . "\n"
        .    '</td>' . "\n"
        .    '<td>' . "\n"
        .    claro_html_textarea_editor('message', $message)
        .    '</td>' . "\n"
        .    '</tr>' . "\n"

        .    '<tr valign="top">' . "\n"
        .    '<td align="right">' . "\n"
        .    '<label for="delete" >' . get_lang('Delete') . '</label>' . "\n"
        .    ' : ' . "\n"
        .    '</td>' . "\n"
        .    '<td>' . "\n"
        .    '<input type="checkbox" name="delete" id="delete" />' . "\n"
        .    '</td>' . "\n"
        .    '</tr>' . "\n"

        .    '<tr>'
        .    '<td>&nbsp;</td>' ."\n"
        .    '<td>'
        .    '<input type="submit" name="submit" value="' . get_lang('Ok') . '" />&nbsp; '
        .    '<input type="submit" name="cancel" value="' . get_lang('Cancel') . '" />'
        .    '</td>' . "\n"
        .    '</tr>' . "\n"
        .    '</table>'. "\n"
        .    '</form>' . "\n"
        .    '<br />' . "\n"
        .    '<center>'
        .    '<a href="'.htmlspecialchars(Url::Contextualize( get_module_url('CLFRM') .'/viewtopic.php?topic=' . $topic_id )) . '" target="_blank">'
        .    get_lang('Topic review')
        .    '</a>'
        .    '</center>'
        .    '<br />' . "\n"
        ;

    } // end // else if ! isset submit

}

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>