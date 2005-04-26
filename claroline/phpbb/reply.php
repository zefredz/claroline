<?php

/***************************************************************************
                            reply.php  -  description
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



require 'functions.php';
require 'config.php';

//need for notification in claro 1.5
include ('../inc/lib/claro_mail.lib.inc.php');

if($_REQUEST['cancel'])
{
	header('Location: viewtopic.php?topic='.$topic.'&forum='.$forum);
}


$pagetitle = 'Post Reply';
$pagetype  = 'reply';


$forumSettingList = get_forum_settings($forum, $topic);

/**
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

    die ('Location: viewtopic.php?topic='.$topic.'&forum='.$forum);
}


$forum_name   = $forumSettingList['forum_name'  ];
$forum_access = $forumSettingList['forum_access'];
$forum_type   = $forumSettingList['forum_type'  ];
$forum_id     = $forum;

if( is_locked($topic, $db) ) error_die ($l_nopostlock);

if( ! does_exists($forum, $db, 'forum') || ! does_exists($topic, $db, 'topic'))
{
    error_die("The forum or topic you are attempting to post to does not exist. Please try again.");
}

include('page_header.php');

if($submit)
{
    // Commented by the Claroline team
    //
    // Either valid user/pass, or valid session. continue with post.. but first:
    // Check that, if this is a private forum, the current user can post here.
    //
    // if ($forum_type == 1)
    // {
    //      if (!check_priv_forum_auth($userdata[user_id], $forum, TRUE, $db))
    //      {
    //          error_die("$l_privateforum $l_nopost");

    if( trim( strip_tags($message) ) == '') error_die($l_emptymsg);


    if($allow_html == 0 || isset($html)) $message = htmlspecialchars($message);


    $message    = addslashes($message);
    $lastName   = addslashes($userdata['last_name']);  // ADDED FOR CLAROLINE
    $firstName  = addslashes($userdata['first_name']); // ADDED FOR CLAROLINE
    $poster_ip  = $REMOTE_ADDR;
    $time       = date('Y-m-d H:i');


    create_new_post($topic, $forum, $userdata['user_id'], 
                    $time, $poster_ip, 
                    $lastName, $firstName, $message);


    trig_topic_notification($topic);

    /*------------------------------------------------------------------------
                            DISPLAY SUCCES MESSAGE
      ------------------------------------------------------------------------*/

    disp_confirmation_message ($l_stored, $forum, $topic);
}
else
{
    // Private forum logic here.

    if ( ! $_uid)    // ADDED BY CLAROLINE: exclude non identified visitors
    {
       error_die('<center>'
                .$langLoginBeforePost1.'<br>'
                .$langLoginBeforePost2.' '
                .'<a href="../../index.php">'.$langLoginBeforePost3.'.</a>'
                .'</center>'
                );
    }               // END ADDED BY CLAROLINE exclude visitors unidentified

?>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
<input type="hidden" name="md5" value="<?php echo $md5; ?>">

<div>
<?php echo $l_body?> : 
<br />
<?php

    if($quote)
    {
        $sql = "SELECT pt.post_text, p.post_time, u.username 
                FROM `".$tbl_posts."` p, 
                     `".$tbl_users."` u, 
                     `".$tbl_posts_text."` pt 
                WHERE p.post_id   = '".$post."' 
                  AND p.poster_id = u.user_id 
                  AND pt.post_id  = p.post_id";
       
        list($m) = claro_sql_query_fetch_all($sql);

        $text  = stripslashes($text);
        $reply = sprintf($l_quotemsg,$m['post_time'],$m['username'],$text);
    }
?>
<?php claro_disp_html_area('message', $reply) ?>
<br />
<input type="hidden" name="forum" value="<?php echo $forum?>">
<input type="hidden" name="topic" value="<?php echo $topic?>">
<input type="hidden" name="quote" value="<?php echo $quote?>">
<input type="submit" name="submit" value="<?php echo $langOk?>">
&nbsp;<input type="submit" name="cancel" value="<?php echo $langCancel?>">
</div>
</form>

<p align="center">
<a href="viewtopic.php?topic=<?php echo $topic ?>&forum=<?php echo $forum ?>" 
   target="_blank">
<?php echo $l_topicreview ?>
</a>
<?php
} // end else if submit

require('page_tail.php');
?>
