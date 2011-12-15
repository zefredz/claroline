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

if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);

claro_set_display_mode_available(true);

/**
 *
 * Try to create table (update script error for forum notifications)*
 *
 */
install_module_database_in_course( 'CLFRM', claro_get_current_course_id() );

/*-----------------------------------------------------------------
  Library
 -----------------------------------------------------------------*/

include_once get_path('incRepositorySys') . '/lib/forum.lib.php';

// variables

$allowed = TRUE;
$error = FALSE;

$dialogBox = new DialogBox();
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
                    || (  claro_is_group_tutor() && !claro_is_course_manager());
                    // (  claro_is_group_tutor()
                    //  is added to give admin status to tutor
                    // && !claro_is_course_manager())
                    // is added  to let course admin, tutor of current group, use student mode

if ( ! claro_is_user_authenticated() || ! claro_is_in_a_course() )
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
        || ( ! is_null($forumSettingList['idGroup'])
            && ( !claro_is_in_a_group() || ! claro_is_group_allowed() || $forumSettingList['idGroup'] != claro_get_current_group_id() ) ) )
    {
        // NOTE : $forumSettingList['idGroup'] != claro_get_current_group_id() is necessary to prevent any hacking
        // attempt like rewriting the request without $cidReq. If we are in group
        // forum and the group of the concerned forum isn't the same as the session
        // one, something weird is happening, indeed ...
        $allowed = FALSE;
        $dialogBox->error( get_lang('Not allowed') );
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
            $userLastname  = claro_get_current_user_data('lastName');
            $userFirstname = claro_get_current_user_data('firstName');
            $poster_ip     = $_SERVER['REMOTE_ADDR'];

            $time = date('Y-m-d H:i');

            // prevent to go further if the fields are actually empty
            if ( strip_tags($message,'<img><audio><video><embed><object><canvas><iframe>') == '' || $subject == '' )
            {
                $dialogBox->error( get_lang('You cannot post an empty message') );
                $error = TRUE;
            }

            if ( !$error )
            {
                // record new topic
                $topic_id = create_new_topic($subject, $time, $forum_id, claro_get_current_user_id(), $userFirstname, $userLastname);
                if ( $topic_id )
                {
                    create_new_post($topic_id, $forum_id, claro_get_current_user_id(), $time, $poster_ip, $userLastname, $userFirstname, $message);
                }
                // notify eventmanager that a new message has been posted

                $eventNotifier->notifyCourseEvent('forum_new_topic',claro_get_current_course_id(), claro_get_current_tool_id(), $forum_id."-".$topic_id, claro_get_current_group_id(), 0);
                
                // notify by mail that a new topic has been created
                trig_forum_notification($forum_id);
                /*if( get_conf('clfrm_notification_enabled', true) )
                {
                    $courseSender = claro_get_current_user_data('firstName') . ' ' . claro_get_current_user_data('lastName');
                    $courseOfficialCode = claro_get_current_course_data('officialCode');
                    $title = get_lang('New topic on the forum %forum', array('%forum' => $forum_name));
                    $msgContent = get_lang('A new topic called %topic has been created on the forum %forum', array('%topic' => $subject, '%forum' => $forum_name));
                    
                    // attached resource
                    $body = $msgContent . "\n"
                    .   "\n"
                    ;
    
                    require_once dirname(__FILE__) . '/../messaging/lib/message/messagetosend.lib.php';
                    require_once dirname(__FILE__) . '/../messaging/lib/recipient/userlistrecipient.lib.php';
                    require_once dirname(__FILE__) . '/../inc/lib/course.lib.inc.php';
                    
                    $courseManagers = claro_get_course_manager_id( claro_get_current_course_id() );
                    $userListRecipient = new UserListRecipient;
                    $userListRecipient->addUserIdList( $courseManagers );
                    
                    $message = new MessageToSend(claro_get_current_user_id(),$title,$body);
                    $message->setCourse(claro_get_current_course_id());
                    $message->setTools('CLFRM');
                    
                    $messageId = $userListRecipient->sendMessage($message);                    
                }*/
                

            }

        } // end if submit
    }
}
else
{
    // forum doesn't exists
    $allowed = false;
    $dialogBox->error( get_lang('Not allowed') );
}

/*=================================================================
  Display Section
 =================================================================*/

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Forums'), 'index.php' );
$noPHP_SELF       = true;

$out = '';

// display tool title
$out .= claro_html_tool_title(get_lang('Forums'), $is_allowedToEdit ? 'help_forum.php' : false);

if ( ! $allowed )
{
    $out .= $dialogBox->render();
}
else
{
    // Display new topic page

    if ( isset($_REQUEST['submit']) && !$error)
    {
        // Display success message
        $out .= disp_confirmation_message (get_lang('Your message has been entered'), $forum_id, $topic_id);

    }
    else
    {
        if ( $error )
        {
            // display error message
            $out .= $dialogBox->render();
        }

        $out .= disp_forum_breadcrumb($pagetype, $forum_id, $forum_name)
        .    claro_html_menu_horizontal(disp_forum_toolbar($pagetype, $forum_id, 0, 0))


        .    '<form action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" method="post">' . "\n"
        .    '<input type="hidden" name="forum" value="' . $forum_id . '" />' . "\n"
        .    claro_form_relay_context()
        .    '<table border="0" width="100%">' . "\n"
        .    '<tr valign="top">' . "\n"
        .    '<td align="right"><label for="subject">' . get_lang('Subject') . '</label> : </td>'
        .    '<td><input type="text" name="subject" id="subject" size="50" maxlength="100" value="' . htmlspecialchars($subject) . '" /></td>'
        .    '</tr>'
        .    '<tr  valign="top">' . "\n"
        .    '<td align="right"><br />' . get_lang('Message body') . ' :</td>';

        if ( !empty($message) ) $content = $message;
        else                    $content = '';

        $out .= '<td>'
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

$claroline->display->body->appendContent($out);

echo $claroline->display->render();
?>