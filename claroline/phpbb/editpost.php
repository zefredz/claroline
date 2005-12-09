<?php  // $Id$
/**
 * CLAROLINE
 *
 * Script for forum tool
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

// initialise variables

$last_visit = $_user['lastLogin'];

$last_visit = $_user['lastLogin'];
$error = FALSE;
$allowed = TRUE;
$error_message = '';

$pagetitle = get_lang('EditPost');
$pagetype  = 'editpost';

/*=================================================================
  Main Section
 =================================================================*/

if ( isset($_REQUEST['post_id']) ) $post_id = (int) $_REQUEST['post_id'];
else                               $post_id = 0;

$is_allowedToEdit = claro_is_allowed_to_edit() 
                    || ( $is_groupTutor && !$is_courseAdmin);
                    // ( $is_groupTutor 
                    //  is added to give admin status to tutor 
                    // && !$is_courseAdmin)
                    // is added  to let course admin, tutor of current group, use student mode

$postSettingList =  get_post_settings($post_id);

if ( $postSettingList && $is_allowedToEdit )
{
     $topic_id        = $postSettingList['topic_id'];

    $forumSettingList = get_forum_settings($postSettingList['forum_id']);

    $forum_name         = stripslashes($forumSettingList['forum_name']);
    $forum_access       = $forumSettingList['forum_access'];
    $forum_type         = $forumSettingList['forum_type'  ];
    $forum_groupId      = $forumSettingList['idGroup'     ];
    $forum_cat_id       = $forumSettingList['cat_id'      ];
    $forum_topicId      = $forumSettingList['topic_id'    ];

    /* 
     * Check if the topic isn't attached to a group,  or -- if it is attached --, 
     * check the user is allowed to see the current group forum.
     */
    
    if (   ! is_null($forumSettingList['idGroup']) 
        && ( $forumSettingList['idGroup'] != $_gid || ! $is_groupAllowed) )
    {
        // NOTE : $forumSettingList['idGroup'] != $_gid is necessary to prevent any hacking 
        // attempt like rewriting the request without $cidReq. If we are in group 
        // forum and the group of the concerned forum isn't the same as the session 
        // one, something weird is happening, indeed ...
        $allowed = false;
        $error_message = get_lang('NotAllowed') ;
    } 
    else 
    {
        if ( isset($_REQUEST['cancel']) )
        {
            header('Location: viewtopic.php?topic=' . $topic_id );
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
            list($day, $time) = split(' ', $postSettingList['post_time']);

            $posterdata       = get_userdata_from_id($poster_id);
            $date             = date('Y-m-d H:i');

            if ( isset($_REQUEST['message']) ) 
            {
                $message = $_REQUEST['message'];

                if ( get_conf('allow_html') == 0 || isset($html) ) $message = htmlspecialchars($message);
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
                delete_post($post_id, $topic_id, $forum_id, $posterdata['user_id']);
            }

        } // end submit management
        else
        {
            /*==========================
                  EDIT FORM BUILDING
              ==========================*/
            $postSettingList  = get_post_settings($post_id);

            list($day, $time) = split(' ', $postSettingList['post_time']);
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
    $error_message = get_lang('NotAllowed');
}

/*=================================================================
  Display Section
 =================================================================*/
 
if ( $forum_cat_id == 1 && ($is_groupMember || $is_groupTutor || $is_courseAdmin ) )
{
    $interbredcrump[]  = array ('url'=>'../group/group.php', 'name'=> get_lang('Groups'));
    $interbredcrump[]= array ("url"=>"../group/group_space.php", 'name'=> $_group['name']);
}

$interbredcrump[] = array ('url' => 'index.php', 'name' => get_lang('Forums'));
$noPHP_SELF       = true;

include $includePath . '/claro_init_header.inc.php';
    
// Forum Title

echo claro_disp_tool_title(get_lang('Forums'), $is_allowedToEdit ? 'help_forum.php' : false);

if ( !$allowed || !$is_allowedToEdit )
{
      echo claro_disp_message_box($error_message); 
}
else
{
 
    if ( isset($_REQUEST['submit']) && !$error)
    {
        if ( ! isset($_REQUEST['delete']) )
        {
            disp_confirmation_message (get_lang('stored'), $forum_id, $topic_id);
        }
        else
        {
            disp_confirmation_message (get_lang('deleted'), $forum_id);
        }
    }
    else
    {

        if ( $error )
        {
            echo claro_disp_message_box($error_message);
        }

        disp_forum_toolbar($pagetype, $forum_id, $topic_id, 0);
        disp_forum_breadcrumb($pagetype, $forum_id, $forum_name, $subject);

        echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post" >' . "\n"
            . '<input type="hidden" name="post_id" value="' . $post_id . '" />' . "\n"
            . '<table border="0">' . "\n"
            . '<tr valign="top">' . "\n"
            . '<td colspan="2"><b>' . $pagetitle . '</b></td>' . "\n"
            . '</tr>' . "\n";

        $first_post = is_first_post($topic_id, $post_id);

        if ( $first_post )
        {
            echo '<tr valign="top">' . "\n"
                . '<td align="right">' . "\n"
                . '<label for="subject">' . get_lang('subject') . '</label> : '
                . '</td>' . "\n"
                . '<td>' . "\n"
                . '<input type="text" name="subject" id="subject" size="50" maxlength="100" value="' . htmlspecialchars($subject) . '" />'
                . '</td>' . "\n"
                . '</tr>' . "\n";
        }

        echo '<tr valign="top">' . "\n"
            . '<td align="right"><br />' . get_lang('body') . ' : </td>' . "\n"
            . '<td>' . "\n"
            .claro_disp_html_area('message', htmlspecialchars($message))
            .'</td>' . "\n"
            . '</tr>' . "\n"

            . '<tr valign="top">' . "\n"
            . '<td align="right"><label for="delete" >' . get_lang('delete') . '</label> : </td>' . "\n"
            . '<td>' . "\n"
            . '<input type="checkbox" name="delete" id="delete" />' . "\n"
            . '</td>' . "\n"
            . '</tr>' . "\n"

            . '<tr>'
            . '<td>&nbsp;</td>' ."\n"
            . '<td>'
            . '<input type="submit" name="submit" value="' . get_lang('Ok') . '" />' . "\n"
            . '<input type="submit" name="cancel" value="' . get_lang('Cancel') . '" />'
            . '</td>' . "\n"
            . '</tr>' . "\n"
            . '</table>'. "\n"

            . '<br />' . "\n"
            . '<center>'
            . '<a href="viewtopic.php?topic=' . $topic_id . '" target="_blank">'
            . get_lang('topicreview')
            . '</a>'
            . '</center>'
            . '<br />' ."\n";

    } // end // else if ! isset submit


}

/*-----------------------------------------------------------------
  Display Forum Footer
 -----------------------------------------------------------------*/

include($includePath . '/claro_init_footer.inc.php');

?>
