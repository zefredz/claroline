<?php
session_start();
include('../inc/conf/claro_main.conf.php');

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
include('extention.inc');

//neede for notification in claro 1.5

$TABLEUSER = $mainDbName.".`user`";
include ('../inc/lib/claro_mail.lib.inc.php');


if($cancel)
{
	header("Location: viewtopic.php?topic=$topic&forum=$forum");
}

include('functions.php');
include('config.php');
require('auth.php');
$pagetitle = "Post Reply";
$pagetype  = "reply";


$forumSettingList = get_forum_settings($forum, $topic);

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


$forum_name   = $forumSettingList['forum_name'];
$forum_access = $forumSettingList['forum_access'];
$forum_type   = $forumSettingList['forum_type'];
$forum_id     = $forum;

if( is_locked($topic, $db) ) error_die ($l_nopostlock);

if( ! does_exists($forum, $db, 'forum') || ! does_exists($topic, $db, 'topic'))
{
    error_die("The forum or topic you are attempting to post to does not exist. Please try again.");
}

include('page_header.php');

if($submit)
{
    if( trim( strip_tags($message)) == '') error_die($l_emptymsg);

    if ( ! $user_logged_in)
    {
        if($username == '' && $password == '' && $forum_access == 2)
        {
            // Not logged in, and username and password are empty 
            // and forum_access is 2 (anon posting allowed)
            $userdata = array('user_id' => -1);
        }
        else if($username == '' || $password == '') // no valid session, need to check user/pass.
        {
            error_die($l_userpass);
        }

        if( $userdata['user_level'] == -1) error_die($l_userremoved);

        if($userdata['user_id'] != -1) 
        {
            $userdata = get_userdata($username, $db);
            if(md5($password) != $userdata['user_password']) error_die($l_wrongpass);
        }

        if($forum_access == 3 && $userdata['user_level'] < 2) error_die($l_nopost);
        if(is_banned($userdata['user_id'], 'username', $db))  error_die($l_banned);

        if($userdata[user_id] != -1)
        {
             // You've entered your username and password, so we log you in.
             $sessid = new_session($userdata[user_id], $REMOTE_ADDR, $sesscookietime, $db);
             set_session_cookie($sessid, $sesscookietime, $sesscookiename, 
                                $cookiepath, $cookiedomain, $cookiesecure);
        }
    }
    else
    {
        if($forum_access == 3 && $userdata['user_level'] < 2) error_die($l_nopost);
    }

    // Either valid user/pass, or valid session. continue with post.. but first:
    // Check that, if this is a private forum, the current user can post here.
    if (   $forum_type == 1
        && !check_priv_forum_auth($userdata['user_id'], $forum, true, $db) )
    {
        error_die($l_privateforum.' '.$l_nopost);
    }
     
    $poster_ip = $REMOTE_ADDR;

    $is_html_disabled = false;

    if($allow_html == 0 || isset($html))
    {
        $message          = htmlspecialchars($message);
        $is_html_disabled = true;

        if ($quote)
        {
            $edit_by = get_syslang_string($sys_lang, "l_editedby");

            // If it's been edited more than once, there might be old "edited 
            // by" strings with escaped HTML code in them. 
            // We want to fix this up right here:
            $message = preg_replace("#&lt;font\ size\=-1&gt;\[\ $edit_by(.*?)\ \]&lt;/font&gt;#si", '<small>[ ' . $edit_by . '\1 ]</small>', $message);	
        }
    }

    if($allow_bbcode == 1 && !isset($bbcode))
    {
        $message = bbencode($message, $is_html_disabled);
    }

    // MUST do make_clickable() and smile() before changing \n into <br>.
    $message = make_clickable($message);

    if( ! $smile) $message = smile($message);
    
    $message = str_replace("\n", "<BR>", $message);
    $message = censor_string($message, $db);
    $message = addslashes($message);
    $time    = date('Y-m-d H:i');

    // ADDED BY Thomas 20.2.2002

   $nom    = addslashes($nom);
   $prenom = addslashes($prenom);

   // END ADDED BY THOMAS

    //to prevent [addsig] from getting in the way, let's put the sig insert down here.
    if($sig && $userdata[user_id] != -1)
    {
        $message .= "\n[addsig]";
    }

    // CREATE THE POST

    $sql = "INSERT INTO `".$tbl_posts."` 
            SET topic_id  = '".$topic."',
                forum_id  = '".$forum."',
                poster_id = '".$userdata['user_id']."',
                post_time = '".$time."',
                poster_ip = '".$poster_ip."',
                nom       = '".$nom."',
                prenom    = '".$prenom."'";

    $this_post = claro_sql_query_insert_id($sql);

    // RECORD THE POST CONTENT

    if($this_post)
    {
        $sql = "INSERT INTO `".$tbl_posts_text."` 
                SET post_id   = '".$this_post."', 
                    post_text = '".$message."'";

        $result = claro_sql_query($sql);
    }

    $sql = "UPDATE `".$tbl_topics."` 
            SET   topic_replies      =  topic_replies+1, 
                  topic_last_post_id = '".$this_post."',
                  topic_time         = '".$time."' 
            WHERE topic_id           = '".$topic."'";

    $result = claro_sql_query($sql);

    if( $userdata['user_id'] != -1 ) 
    {
        $sql = "UPDATE `".$tbl_users."` 
                SET   user_posts = user_posts+1 
                WHERE user_id = '".$userdata['user_id']."'";

        $result = claro_sql_query($sql);
    }

    // UPDATE THE POST AND TOPIC STATUS FDR THE CURRENT FORUM

    $sql = "UPDATE `".$tbl_forums."` 
            SET   forum_posts        =  forum_posts+1, 
                  forum_last_post_id = '".$this_post."' 
            WHERE forum_id           = '".$forum."'";

    $result = claro_sql_query($sql);

    // added for CLAROLINE 1.5 : send notification for user who subscribed for it

    $sql = "SELECT u.user_id, u.prenom firstname, u.nom lastname
            FROM `".$tbl_user_notify."` AS notif, 
                 ".$TABLEUSER." AS u
            WHERE notif.topic_id = '".$topic."'
            AND   notif.user_id  = u.user_id";

    $notifyResult = claro_sql_query($sql);
    $subject      = get_syslang_string($sys_lang, 'l_notifysubj');

    // send mail to registered user for notification

    while ($list = mysql_fetch_array($notifyResult))
    {
       $message = get_syslang_string($sys_lang, 'l_dear')." ".$list['firstname']." ".$list['lastname'].",\n";
       $message.= get_syslang_string($sys_lang, 'l_notifybody');
       eval("\$message =\"$message\";");
       claro_mail_user($list['user_id'], $message, $subject);
    }

    //  this code is from phpbb 1.4, but not used anymore in claroline 1.5.
    //
    //    $sql = "SELECT t.topic_notify, u.user_email, u.username, u.user_id 
    //            FROM `".$tbl_topics."` t, 
    //                 `".$tbl_users."` u
    //            WHERE t.topic_id     = '".$topic."' 
    //              AND t.topic_poster = u.user_id";
    //
    //    $result = claro_sql_query($sql);
    //
    //    $m = mysql_fetch_array($result,MYSQL_ASSOC);
    //
    //    if($m["topic_notify"] == 1 && $m["user_id"] != $userdata["user_id"])
    //    {
    //        // We have to get the mail body and subject line 
    //        // in the board default language!
    //        $subject = get_syslang_string($sys_lang, "l_notifysubj");
    //        $message = get_syslang_string($sys_lang, "l_notifybody");
    //        eval("\$message =\"$message\";");
    //        mail($m[user_email], $subject, $message, 
    //             "From: $email_from\r\nX-Mailer: phpBB $phpbbversion");
    //    }



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
       error_die("<center>"
            .$langLoginBeforePost1."<br>"
            .$langLoginBeforePost2." "
            ."<a href=../../index.php>".$langLoginBeforePost3.".</a>"
            ."</center>");
    }               // END ADDED BY CLAROLINE exclude visitors unidentified

    if ($forum_type == 1 // check whether that user is allowed to view this private forum.
        && ! check_priv_forum_auth($userdata['user_id'], $forum, true, $db) ) 
    {
        error_die($l_privateforum.' '.$l_nopost);
    }


?>
<form action="<?php echo $PHP_SELF?>" method="POST">
<input type="hidden" name="md5" value="<?php echo $md5; ?>">

<table border="0">

<tr valign="top">
<td align="right"><?php echo $l_body?> : 
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

        $text             = desmile($m['post_text']);
        $text             = str_replace("<BR>", "\n", $text);
        $text             = stripslashes($text);
        $text             = bbdecode($text);
        $text             = undo_make_clickable($text);
        $text             = str_replace('[addsig]', '', $text);
        $syslang_quotemsg = get_syslang_string($sys_lang, 'l_quotemsg');
        eval("\$reply = \"$syslang_quotemsg\";");
    }
?>
</td>
<td>
<?php claro_disp_html_area('message', $reply) ?>
</td>
</tr>

<tr>
<td></td>
<td>
<input type="hidden" name="forum" value="<?php echo $forum?>">
<input type="hidden" name="topic" value="<?php echo $topic?>">
<input type="hidden" name="quote" value="<?php echo $quote?>">
<input type="submit" name="submit" value="<?php echo $l_submit?>">
&nbsp;<input type="submit" name="cancel" value="<?php echo $l_cancelpost?>">
</td>
</tr>
</table>
</form>
<p align="center">
<a href="viewtopic.php?topic=<?php echo $topic ?>&forum=<?php echo $forum ?>" 
   target=\"_blank\">
<?php echo $l_topicreview ?>
</a>
<?php
} // end else if submit

require('page_tail.php');
?>