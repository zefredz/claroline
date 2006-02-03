<?php // $ Id: $
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

$tlabelReq = 'CLFRM___';

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

include $includePath . '/lib/forum.lib.php';
require $includePath . '/lib/pager.lib.php';

// for notification
include $includePath . '/lib/claro_mail.lib.inc.php';

$error = FALSE;
$error_message = '';
$allowed = TRUE;
$pagetitle = get_lang('PostReply');
$pagetype  = 'reply';

/*=================================================================
  Main Section
 =================================================================*/

if ( isset($_REQUEST['forum']) ) $forum_id = (int) $_REQUEST['forum'];
else                             $forum_id = 0;

if ( isset($_REQUEST['topic']) ) $topic_id = (int) $_REQUEST['topic'];
else                             $topic_id = 0;
        
if ( isset($_REQUEST['message']) ) $message = $_REQUEST['message'];
else                               $message = '';

if ( isset($_REQUEST['cancel']) )
{
    header('Location: viewtopic.php?topic=' . $topic_id . '&forum='.$forum_id);
    exit();
}

$topicSettingList = get_topic_settings($topic_id); 

if ( ! $_uid || ! $_cid) claro_disp_auth_form(true);
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
            && ( $forumSettingList['idGroup'] != $_gid || ! $is_groupAllowed) ) )
    {
        // NOTE : $forumSettingList['idGroup'] != $_gid is necessary to prevent any hacking 
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

            $lastName   = $_user['lastName'];
            $firstName  = $_user['firstName'];
            $poster_ip  = $_SERVER['REMOTE_ADDR'];
            $time       = date('Y-m-d H:i');

            create_new_post($topic_id, $forum_id, $_uid, $time, $poster_ip, $lastName, $firstName, $message);

            // notify eventmanager that a new message has been posted

            $eventNotifier->notifyCourseEvent("forum_answer_topic",$_cid, $_tid, $forum_id."-".$topic_id, $_gid, "0");

            trig_topic_notification($topic_id); 
        }
        else
        {
            $error = TRUE;
            $error_message = get_lang('emptymsg');
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
 
if (   isset($forum_cat_id) && $forum_cat_id == GROUP_FORUMS_CATEGORY 
    && $is_groupAllowed)
{
    $interbredcrump[]  = array ('url'=>'../group/group.php', 'name'=> get_lang('Groups'));
    $interbredcrump[]= array ("url"=>"../group/group_space.php", 'name'=> $_group['name']);
}

$interbredcrump[] = array ('url' => 'index.php', 'name' => get_lang('Forums'));
$noPHP_SELF       = true;

include $includePath . '/claro_init_header.inc.php';

$pagetitle = get_lang('topictitle');
$pagetype  = 'reply';

$is_allowedToEdit = claro_is_allowed_to_edit(); 

echo claro_disp_tool_title(get_lang('Forums'), 
                      $is_allowedToEdit ? 'help_forum.php' : false);

if ( !$allowed )
{
    // not allowed
    echo claro_html::message_box($error_message);
}
else
{

    if ( isset($_REQUEST['submit']) && !$error )
    {
        // DISPLAY SUCCES MESSAGE
        disp_confirmation_message (get_lang('stored'), $forum_id, $topic_id);
    }
    else
    {
        if ( $error )
        {
            echo claro_html::message_box($error_message);
        }

        disp_forum_toolbar($pagetype, $forum_id, 0, $topic_id);
        disp_forum_breadcrumb($pagetype, $forum_id, $forum_name, $topic_title);

        echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n"
            . '<input type="hidden" name="forum" value="' . $forum_id . '" />' . "\n"
            . '<input type="hidden" name="topic" value="' . $topic_id . '" />' . "\n";
        
        echo '<table border="0">' . "\n"
            . '<tr valign="top">' . "\n"
            . '<td align="right"><br />' . get_lang('body') . '&nbsp;:</td>'
            . '<td>'
            .claro_disp_html_area('message', htmlspecialchars($message))
            .'</td>'
            . '</tr>'
            . '<tr valign="top"><td>&nbsp;</td>'
            . '<td>'
            . '<input type="submit" name="submit" value="' . get_lang('Ok') . '" />&nbsp;'
            . '<input type="submit" name="cancel" value="' . get_lang('Cancel') . '" />'
            . '</tr>'
            . '</table>'
            . '</form>' ;

        echo '<p align="center"><a href="viewtopic.php?topic=' . $topic_id . '&forum=' . $forum_id . '" target="_blank">' . get_lang('topicreview') . '</a>';

    } // end else if submit
}

// Display Forum Footer

include($includePath.'/claro_init_footer.inc.php');

?>
