<?php  
session_start();
include('../inc/conf/claro_main.conf.php');

/***************************************************************************
                            newtopic.php  -  description
                             -------------------
    begin                : Sat June 17 2000
    copyright            : (C) 2001 The phpBB Group
    email                : support@phpbb.com

    $Id$

 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/
include('extention.inc');
// Set the error reporting to a sane value, 'cause we haven't included auth.php yet..
error_reporting  (E_ERROR | E_WARNING | E_PARSE); // This will NOT report uninitialized variables

if($cancel)
{
	header('Location: viewforum.php?forum='.$forum);
	exit();
}

include 'functions.php';
include 'config.php';
require 'auth.php';

$pagetitle = 'New Topic';
$pagetype =  'newtopic';

$userFirstName = $_user['firstName'];
$userLastName  = $_user['lastName' ];

$forumSettingList = get_forum_settings($forum);

$forum_name 		= $forumSettingList['forum_name'  ];
$forum_access 		= $forumSettingList['forum_access'];
$forum_type 		= $forumSettingList['forum_type'  ];
$forum_groupId 		= $forumSettingList['idGroup'     ];
$forum_groupname	= $forumSettingList['nameGroup'   ];
$forum_id 			= $forum;

// Check if the forum isn't attached to a group, 
// or -- if it is attached --, check the user 
// is allowed to see the current group forum.

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

    die ('<center>not allowed</center>');
}



if($submit)
{

    /*------------------------------------------------------------------------
                                PREPARE THE DATA
      ------------------------------------------------------------------------*/
    
    $subject = strip_tags($subject);
    
    if(trim( strip_tags($message)) == '' || trim($subject) == '')
    {
        error_die($l_emptymsg);
    }
    
    // set as anonymous phpBB user. we try to do it better soon
    // Claroline team
        
    $userdata = array('user_id' => -1);
    
    // Commented by the Claroline team
    //
    // Either valid user/pass, or valid session. continue with post.. but first:
    // Check that, if this is a private forum, the current user can post here.
    //
    // if ($forum_type == 1)
    // {
    //		if (!check_priv_forum_auth($userdata[user_id], $forum, TRUE, $db))
    // 		{
    //			error_die("$l_privateforum $l_nopost");

    $is_html_disabled = false;

    if($allow_html == 0 || isset($html))
    {
        $message          = htmlspecialchars($message);
        $is_html_disabled = true;
    }

    if($allow_bbcode == 1 && !($HTTP_POST_VARS[bbcode]))
    {
        $message = bbencode($message, $is_html_disabled);
    }

    // MUST do make_clickable() and smile() before changing \n into <br>.
    
    $message = make_clickable($message);
    
    if( ! $smile) $message = smile($message);

    $message   = str_replace("\n", "<BR>", $message);

    $message   = censor_string($message, $db);
    $subject   = censor_string($subject, $db);

    $subject   = strip_tags($subject);

    $message   = addslashes($message);
    $subject   = addslashes($subject);

    $poster_ip = $REMOTE_ADDR;
    $time      = date('Y-m-d H:i');

    // ADDED FOR CLAROLINE
    $userLastName    = addslashes($userLastName);
    $userFirstName   = addslashes($userFirstName);
    // END ADDED FOR CLAROLINE

    /*------------------------------------------------------------------------
                            RECORD THE DATA
      ------------------------------------------------------------------------*/

    // CREATE THE TOPIC

    $sql = "INSERT INTO `".$tbl_topics."` 
            SET topic_title  = '".$subject."', 
                topic_poster = '".$userdata['user_id']."', 
                forum_id     = '".$forum."', 
                topic_time   = '".$time."', 
                topic_notify = 1,
                nom          = '".$userLastName."', 
                prenom       = '".$userFirstName."'";

    $topic_id = claro_sql_query_insert_id($sql);

    // CREATE THE POST

    if ($topic_id)
    {
        $sql = "INSERT INTO `".$tbl_posts."`
                SET topic_id  = '".$topic_id."', 
                    forum_id  = '".$forum."', 
                    poster_id = '".$userdata['user_id']."', 
                    post_time = '".$time."', 
                    poster_ip = '".$poster_ip."', 
                    nom       = '".$userLastName."', 
                    prenom    = '".$userFirstName."'";

        $post_id = claro_sql_query_insert_id($sql);
    }

    // RECORD THE POST CONTENT

    if($post_id)
    {
        $sql = "INSERT INTO `".$tbl_posts_text."` 
                SET post_id   = '".$post_id."', 
                    post_text = '".$message."'";
        
        $result = claro_sql_query($sql);

        $sql = "UPDATE `".$tbl_topics."` 
                SET   topic_last_post_id = '".$post_id."' 
                WHERE topic_id = '".$topic_id."'";

        $result = claro_sql_query($sql);
    }

    // UPDATE THE POST NUMBER STATUS FOR THE CURRENT USER

    if($userdata['user_id'] != -1)
    {
        $sql = "UPDATE `".$tbl_users."` 
                SET   user_posts = user_posts+1 
                WHERE user_id = '".$userdata['user_id']."'";
        
        $result = claro_sql_query($sql);
    }

    // UPDATE THE POST AND TOPIC STATUS FDR THE CURRENT FORUM

    $sql = "UPDATE `".$tbl_forums."` 
            SET   forum_posts        = forum_posts+1, 
                  forum_topics       = forum_topics+1, 
                  forum_last_post_id = '".$post_id."' 
            WHERE forum_id           = '".$forum."'";

    $result = claro_sql_query($sql);

    /*------------------------------------------------------------------------
                            DISPLAY SUCCES MESSAGE
      ------------------------------------------------------------------------*/

    include('page_header.php');

    disp_confirmation_message ($l_stored, $forum_id, $topic_id);

} // end if submit
else
{
    include('page_header.php');

    if ( ! $_uid)    // ADDED BY CLAROLINE: exclude non identified visitors
    {
        error_die($langLoginBeforePost1.'<br />'
                  .$langLoginBeforePost2
                  ."<a href=../../index.php>".$langLoginBeforePost3.".</a>");
    }               // END ADDED BY CLAROLINE exclude visitors unidentified
    
?>

<form action="<?php echo $php_self?>" method="post">

<table border="0">

<tr valign="top">

<td align="right"><label for="subject"><?php echo $l_subject?></label> :</td>
<td><input type="text" name="subject" id="subject" size="50" maxlength="100"></td>

</tr>

<tr valign="top">

<td align="right">
<?php echo $l_body?> :
<br><br>
</td>

<td>
<?php claro_disp_html_area('message'); ?>
</td>

</tr>

<tr>

<td></td>

<td>
<input type="hidden" name="forum" value="<?php echo $forum?>">
<input type="submit" name="submit" value="<?php echo $l_submit?>">
&nbsp;<input type="submit" name="cancel" value="<?php echo $l_cancelpost?>">
</td>

</tr>

</table>

</form>

<?php
}

require('page_tail.php');
?>