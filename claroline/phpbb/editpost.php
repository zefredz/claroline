<?php  // $Id$
/**
 * CLAROLINE
 *
 * Script for forum tool
 *
 * @version 1.6 $Revision$
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

include '../inc/claro_init_global.inc.php';

claro_unquote_gpc();

$nameTools = $langForums;

if ( !isset($_cid) ) claro_disp_select_course();
if ( !isset($is_courseAllowed) || ! $is_courseAllowed ) claro_disp_auth_form();

claro_set_display_mode_available(true);

/*-----------------------------------------------------------------
  Stats
 -----------------------------------------------------------------*/

include $includePath.'/lib/events.lib.inc.php';
event_access_tool($_tid, $_courseTool['label']);

/*-----------------------------------------------------------------
  Library
 -----------------------------------------------------------------*/

include $includePath . '/lib/forum.lib.php';

/*-----------------------------------------------------------------
  DB table names
 -----------------------------------------------------------------*/

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_cdb_names = claro_sql_get_course_tbl();

$tbl_forums           = $tbl_cdb_names['bb_forums'];
$tbl_topics           = $tbl_cdb_names['bb_topics'];

$tbl_posts            = $tbl_cdb_names['bb_posts'];
$tbl_posts_text       = $tbl_cdb_names['bb_posts_text'];

$tbl_group_properties = $tbl_cdb_names['group_property'];
$tbl_student_group	  = $tbl_cdb_names['group_team'];
$tbl_user_group       = $tbl_cdb_names['group_rel_team_user'];
$tbl_course_user      = $tbl_mdb_names['rel_course_user'];
$tbl_group_properties = $tbl_cdb_names['group_property'];
$tbl_users            = $tbl_mdb_names['user'];

// initialise variables

$last_visit = $_user['lastLogin'];

$last_visit = $_user['lastLogin'];
$error = FALSE;
$allowed = TRUE;
$error_message = '';
	
$pagetitle = 'Edit Post';
$pagetype  = 'editpost';

/*=================================================================
  Main Section
 =================================================================*/

if ( isset($_REQUEST['forum']) ) $forum_id = (int) $_REQUEST['forum'] ;
else                             $forum_id = 0;
if ( isset($_REQUEST['topic']) ) $topic_id = (int) $_REQUEST['topic'];
else                             $topic_id = 0;
if ( isset($_REQUEST['post_id']) ) $post_id = (int) $_REQUEST['post_id'];
else                               $post_id = 0;

$post_exists = does_exists($post_id, 'post');

$is_allowedToEdit = claro_is_allowed_to_edit() 
                    || ( $is_groupTutor && !$is_courseAdmin);
                    // ( $is_groupTutor 
                    //  is added to give admin status to tutor 
                    // && !$is_courseAdmin)
                    // is added  to let course admin, tutor of current group, use student mode

if ( $post_exists && $is_allowedToEdit )
{

  	$forumSettingList = get_forum_settings($forum_id);
	
	$forum_name 		= stripslashes($forumSettingList['forum_name']);
	$forum_access 		= $forumSettingList['forum_access'];
	$forum_type 		= $forumSettingList['forum_type'  ];
	$forum_groupId 		= $forumSettingList['idGroup'     ];
    $forum_cat_id       = $forumSettingList['cat_id'      ];

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
	    $allowed = FALSE;
        $error_message = $langNotAllowed ;
	} 
    else 
    {

		if ( isset($_REQUEST['submit']) )
		{
	        /*-----------------------------------------------------------------
			  Edit Post
	         -----------------------------------------------------------------*/
	          
	        $sql = "SELECT poster_id, forum_id, topic_id, post_time 
	                FROM `".$tbl_posts."` 
	                WHERE post_id = '".$post_id."'";
	
			$myrow = claro_sql_query_fetch_all($sql);
	        if (count($myrow) == 1) $myrow = $myrow[0];
	        else                    error_die($err_db_retrieve_data);
			          
			$poster_id        = $myrow['poster_id'];
			$forum_id         = $myrow['forum_id' ];
			$topic_id         = $myrow['topic_id' ];
			$this_post_time   = $myrow['post_time'];
			list($day, $time) = split(' ', $myrow['post_time']);
			$posterdata       = get_userdata_from_id($poster_id, $db);
			$date             = date('Y-m-d H:i');
	
	        if ( isset($_REQUEST['message']) ) 
	        {
	            $message = $_REQUEST['message'];
	
	            if ( $allow_html == 0 || isset($html) ) $message = htmlspecialchars($message);
	
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
	
	        $sql = "SELECT p.post_id, p.topic_id, p.forum_id, p.poster_id, 
	                       p.post_time, p.poster_ip, p.nom , p.prenom,
	                       pt.post_text,
			               u.username, u.user_id,
			               t.topic_title, t.topic_notify
	                       
			        FROM `".$tbl_posts."` p, `".$tbl_users."` u, 
			             `".$tbl_topics."` t, `".$tbl_posts_text."` pt,
					     `".$tbl_forums."` f
	
			        WHERE p.post_id   = '" . $post_id . "'
	                  AND p.topic_id  = '" . $topic_id . "'
	                  AND f.forum_id  = '" . $forum_id . "'
	                  AND pt.post_id  = p.post_id
	                  AND p.topic_id  = t.topic_id
	                  AND p.forum_id  = f.forum_id
	                  AND p.poster_id = u.user_id";
	
			$myrow = claro_sql_query_fetch_all($sql);
	        
	        if (count($myrow) == 1) $myrow = $myrow[0];
	        else
	        {
	            $allowed = FALSE;
	            $error_message = 'unexisting forum';
	        }
	        
            $subject = $myrow['topic_title'];
			$message = $myrow['post_text'];
	
			// Special handling for </textarea> tags in the message, which can break the editing form..
			$message = preg_replace('#</textarea>#si', '&lt;/TEXTAREA&gt;', $message);
	
			list($day, $time) = split(' ', $myrow['post_time']);
	    }
    }
}
else
{
    // post doesn't exist or not allowed to edit post
    $allowed = FALSE;
    $error_message = $langNotAllowed;
}

/*=================================================================
  Display Section
 =================================================================*/

include $includePath . '/claro_init_header.inc.php';
    
// Forum Title

claro_disp_tool_title($langForums, $is_allowedToEdit ? 'help_forum.php' : false);

if ( !$allowed || !$is_allowedToEdit )
{
      claro_disp_message_box($error_message); 
}
else
{
 
    if ( isset($_REQUEST['submit']) && !$error)
    {
		if ( ! isset($_REQUEST['delete']) )
        {
            disp_confirmation_message ($l_stored, $forum_id, $topic_id);
        }
        else
        {
            disp_confirmation_message ($l_deleted, $forum_id);
        }
    }
    else
    {

        if ( $error )
        {
            claro_disp_message_box($error_message);
        }

        disp_forum_toolbar($pagetype, $forum_id, $topic_id, 0);
        disp_forum_breadcrumb($pagetype, $forum_id, $forum_name, $subject);

        echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post" >' . "\n"
            . '<input type="hidden" name="post_id" value="' . $post_id . '" />' . "\n"
            . '<input type="hidden" name="forum" value="' . $forum_id . '" />' . "\n"
            . '<table border="0">' . "\n"
            . '<tr valign="top">' . "\n"
            . '<td colspan="2"><b>' . $pagetitle . '</b></td>' . "\n"
            . '</tr>' . "\n";

        $first_post = is_first_post($topic_id, $post_id);

        if ( $first_post )
        {
            echo '<tr valign="top">' . "\n"
                . '<td align="right">' . "\n"
                . '<label for="subject">' . $l_subject . '</label> : '
                . '</td>' . "\n"
                . '<td>' . "\n"
                . '<input type="text" name="subject" id="subject" size="50" maxlength="100" value="' . htmlspecialchars($myrow['topic_title']) . '" />'
                . '</td>' . "\n"
                . '</tr>' . "\n";
        }

        echo '<tr valign="top">' . "\n"
            . '<td align="right"><br />' . $l_body . ' : </td>' . "\n"
            . '<td>' . "\n";
        
        claro_disp_html_area('message', htmlspecialchars($message));
        
        echo '</td>' . "\n"
            . '</tr>' . "\n";

        echo '<tr valign="top">' . "\n"
            . '<td align="right"><label for="delete" >' . $l_delete . '</label> : </td>' . "\n"
            . '<td>' . "\n"
            . '<input type="checkbox" name="delete" id="delete">' . "\n"
            . '</td>' . "\n"
            . '</tr>' . "\n";

        echo '<tr>'
            . '<td>&nbsp;</td>'
            . '<td>'
            . '<input type="submit" name="submit" value="' . $langSubmit . '">' . "\n"
            . '</td>' . "\n"
            . '</tr>' . "\n"
            . '</table>'. "\n";

        echo '<br>' . "\n"
            . '<center>'
            . '<a href="viewtopic.php?topic=' . $topic_id . '&amp;forum=' . $forum_id . '" target="_blank">' . $l_topicreview . '</a>'
            . '</center>'
            . '<br />';

    } // end // else if ! isset submit


}

/*-----------------------------------------------------------------
  Display Forum Footer
 -----------------------------------------------------------------*/

echo  '<br />
<center>
<small>Copyright &copy; 2000 - 2001 <a href="http://www.phpbb.com/" target="_blank">The phpBB Group</a></small>
</center>';

include($includePath.'/claro_init_footer.inc.php');

?>
