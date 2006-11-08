<?php // $Id$
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
  Initialize
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

// variables

$allowed = TRUE;
$error = FALSE;

$error_message = '';
$pagetype =  'newtopic';

/*=================================================================
  Main Section
 =================================================================*/

if ( isset($_REQUEST['forum']) ) $forum_id = (int) $_REQUEST['forum'];
else                             $forum_id = 0;

if ( isset( $_REQUEST['cancel'] ) )
{
    claro_redirect('viewforum.php?forum='.$forum_id);
    exit();
}

if ( isset($_REQUEST['subject']) ) $subject = $_REQUEST['subject'];
else                               $subject = '';

// XSS
$subject = strip_tags( $subject );

if ( isset($_REQUEST['message']) ) $message = $_REQUEST['message'];
else                               $message = '';

// XSS
$message = preg_replace( '/<script[^\>]*>|<\/script>|(onabort|onblur|onchange|onclick|ondbclick|onerror|onfocus|onkeydown|onkeypress|onkeyup|onload|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onreset|onresize|onselect|onsubmit|onunload)\s*=\s*"[^"]+"/i', '', $message );

$forumSettingList = get_forum_settings($forum_id);

$is_allowedToEdit = claro_is_allowed_to_edit()
                    || ( $is_groupTutor && !$is_courseAdmin);
                    // ( $is_groupTutor
                    //  is added to give admin status to tutor
                    // && !$is_courseAdmin)
                    // is added  to let course admin, tutor of current group, use student mode

if ( ! $_uid || ! $_cid )
{
    claro_disp_auth_form(true);
}
elseif ( $forumSettingList )
{
    $forum_name         = stripslashes($forumSettingList['forum_name']);
    $forum_post_allowed = ($forumSettingList['forum_access'] != 0) ? true : false;
    $forum_type         = $forumSettingList['forum_type'  ];
    $forum_groupId      = $forumSettingList['idGroup'     ];
    $forum_cat_id       = $forumSettingList['cat_id'      ];

    /*
     * Check if the topic isn't attached to a group,  or -- if it is attached --,
     * check the user is allowed to see the current group forum.
     */

    if ( ! $forum_post_allowed
        || (    ! is_null($forumSettingList['idGroup'])
            && ( $forumSettingList['idGroup'] != $_gid || ! $is_groupAllowed) ) )
    {
        // NOTE : $forumSettingList['idGroup'] != $_gid is necessary to prevent any hacking
        // attempt like rewriting the request without $cidReq. If we are in group
        // forum and the group of the concerned forum isn't the same as the session
        // one, something weird is happening, indeed ...
        $allowed = FALSE;
        $error_message = get_lang('Not allowed') ;
    }
    else
    {
        if ( isset($_REQUEST['submit']) )
        {
            // Either valid user/pass, or valid session. continue with post.. but first:
            // Check that, if this is a private forum, the current user can post here.

            /*------------------------------------------------------------------------
                                        PREPARE THE DATA
              ------------------------------------------------------------------------*/

            // SUBJECT
            $subject = trim($subject);

            // MESSAGE
            if ( get_conf('allow_html') == 0 || isset($html) ) $message = htmlspecialchars($message);
            $message = trim($message);

            // USER
            $userLastname  = $_user['lastName'];
            $userFirstname = $_user['firstName'];
            $poster_ip     = $_SERVER['REMOTE_ADDR'];

            $time = date('Y-m-d H:i');

            // prevent to go further if the fields are actually empty
            if ( strip_tags($message) == '' || $subject == '' )
            {
                $error_message = get_lang('You cannot post an empty message');
                $error = TRUE;
            }

            if ( !$error )
            {
                // record new topic
                $topic_id = create_new_topic($subject, $time, $forum_id, $_uid, $userFirstname, $userLastname);
                if ( $topic_id )
                {
                    create_new_post($topic_id, $forum_id, $_uid, $time, $poster_ip, $userLastname, $userFirstname, $message);
                }
                // notify eventmanager that a new message has been posted

                $eventNotifier->notifyCourseEvent('forum_new_topic',$_cid, $_tid, $forum_id."-".$topic_id, $_gid, 0);

            }

        } // end if submit
    }
}
else
{
    // forum doesn't exists
    $allowed = false;
    $error_message = get_lang('Not allowed');
}

/*=================================================================
  Display Section
 =================================================================*/

$interbredcrump[] = array ('url' => 'index.php', 'name' => get_lang('Forums'));
$noPHP_SELF       = true;

include $includePath . '/claro_init_header.inc.php';

// display tool title
echo claro_html_tool_title(get_lang('Forums'), $is_allowedToEdit ? 'help_forum.php' : false);

if ( ! $allowed )
{
    // not allowed
    echo claro_html_message_box($error_message);
}
else
{
    // Display new topic page

    if ( isset($_REQUEST['submit']) && !$error)
    {
        // Display success message
        disp_confirmation_message (get_lang('Your message has been entered'), $forum_id, $topic_id);

    }
    else
    {
        if ( $error )
        {
            // display error message
            echo claro_html_message_box($error_message);
        }

        echo disp_forum_breadcrumb($pagetype, $forum_id, $forum_name)
        .    claro_html_menu_horizontal(disp_forum_toolbar($pagetype, $forum_id, 0, 0))


        .    '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">' . "\n"
        .    '<input type="hidden" name="forum" value="' . $forum_id . '" />' . "\n"

        .    '<table border="0">' . "\n"
        .    '<tr valign="top">' . "\n"
        .    '<td align="right"><label for="subject">' . get_lang('Subject') . '</label> : </td>'
        .    '<td><input type="text" name="subject" id="subject" size="50" maxlength="100" value="' . htmlspecialchars($subject) . '" /></td>'
        .    '<tr  valign="top">' . "\n"
        .    '<td align="right"><br />' . get_lang('Message body') . ' :</td>';

        if ( !empty($message) ) $content = htmlspecialchars($message);
        else                    $content = '';

        echo '<td>'
            .claro_html_textarea_editor('message',$content)
            .'</td>'
            . '</tr>'
            . '<tr  valign="top"><td>&nbsp;</td>'
            . '<td><input type="submit" name="submit" value="' . get_lang('Ok') . '" />&nbsp; '
            . '&nbsp;<input type="submit" name="cancel" value="' . get_lang('Cancel') . '" />' . "\n"
            . '</td></tr>'
            . '</table>'
            .'</form>' . "\n";
    }
} // end allowed

/*-----------------------------------------------------------------
  Display Forum Footer
 -----------------------------------------------------------------*/

include $includePath . '/claro_init_footer.inc.php';

?>