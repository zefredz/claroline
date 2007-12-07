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
$pagetype = "reply";

if ($post_id)
{
	// We have a post id, so include that in the checks..
	$sql = "
    SELECT f.forum_type, f.forum_name, f.forum_access ,
            `g`.`id`	`idGroup`

    FROM `$tbl_forums` f, `$tbl_topics` t, `$tbl_posts` p

    # Check possible attached group ...

    LEFT JOIN `".$tbl_student_group."` `g`
    ON `f`.`forum_id` = `g`.`forumId`

    WHERE f.forum_id = '$forum'

    AND t.topic_id = $topic
    AND p.post_id = $post_id 
    AND t.forum_id = f.forum_id 
    AND p.forum_id = f.forum_id 
    AND p.topic_id = t.topic_id";
}
else
{
	// No post id, just check forum and topic.
	$sql = "
    SELECT  f.forum_type, f.forum_name, f.forum_access ,
            `g`.`id`	`idGroup`
    FROM `$tbl_forums` f, `$tbl_topics` t 
               
    # Check possible attached group ...

    LEFT JOIN `".$tbl_student_group."` `g`
    ON `f`.`forum_id` = `g`.`forumId`

    WHERE f.forum_id = '$forum' 
    AND   t.topic_id = $topic 
    AND   t.forum_id = f.forum_id";	
}

$result = mysql_query($sql, $db) OR error_die("Could not connect to the forums database.");

if (!$myrow = mysql_fetch_array($result,MYSQL_ASSOC))
{
	error_die("The forum/topic you selected does not exist.");
}

if (     is_null($myrow['idGroup']) // there is no group attached to this forum
    || ( $myrow['idGroup'] == $_gid && $is_groupAllowed) )
{
    $forum_name   = $myrow['forum_name'];
    $forum_access = $myrow['forum_access'];
    $forum_type   = $myrow['forum_type'];
    $forum_id     = $forum;

    if(is_locked($topic, $db))
    {
        error_die ($l_nopostlock);
    }

    if(!does_exists($forum, $db, "forum") || !does_exists($topic, $db, "topic"))
    {
        error_die("The forum or topic you are attempting to post to does not exist. Please try again.");
    }

    include('page_header.php');

    if($submit)
    {
        if( trim( strip_tags($message)) == '')
        {
            error_die(
					$l_emptymsg
					."<br />\n"
					."<a href=\"reply.php?topic=".$_REQUEST['topic']
					."&forum=".$_REQUEST['forum']
					."&gidReq=".$_REQUEST['gidReq']
					."&quote=".$_REQUEST['quote']
					."\">".$langBack."</a>"
				);
        }

        if (!$user_logged_in)
        {
            if($username == '' && $password == '' && $forum_access == 2)
            {
                // Not logged in, and username and password are empty and forum_access is 2 (anon posting allowed)
                $userdata = array("user_id" => -1);
            }
            else if($username == '' || $password == '')
            {
                // no valid session, need to check user/pass.
                error_die($l_userpass);
            }

            if($userdata['user_level'] == -1) 
            {
                error_die($l_userremoved);
            }

            if($userdata['user_id'] != -1) 
            {
                $md_pass = md5($password);
                $userdata = get_userdata($username, $db);

                if($md_pass != $userdata["user_password"])
                {
                    error_die($l_wrongpass);
                }	
            }

            if($forum_access == 3 && $userdata['user_level'] < 2)
            {
                error_die($l_nopost);
            }

            if(is_banned($userdata['user_id'], "username", $db)) 
            {
                error_die($l_banned);
            }

            if($userdata['user_id'] != -1)
            {
                 // You've entered your username and password, so we log you in.
                 $sessid = new_session($userdata['user_id'], $REMOTE_ADDR, $sesscookietime, $db);
                 set_session_cookie($sessid, $sesscookietime, $sesscookiename, $cookiepath, $cookiedomain, $cookiesecure);
            }
        }
        else
        {
            if($forum_access == 3 && $userdata['user_level'] < 2) 
            {
                error_die($l_nopost);
            }
        }

        // Either valid user/pass, or valid session. continue with post.. but first:
        // Check that, if this is a private forum, the current user can post here.
        if ($forum_type == 1)
        {
            if (!check_priv_forum_auth($userdata['user_id'], $forum, TRUE, $db))
            {
                error_die("$l_privateforum $l_nopost");
            }
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

                // If it's been edited more than once, there might be old "edited by" strings with
                // escaped HTML code in them. We want to fix this up right here:
                $message = preg_replace("#&lt;font\ size\=-1&gt;\[\ $edit_by(.*?)\ \]&lt;/font&gt;#si", '<small>[ ' . $edit_by . '\1 ]</small>', $message);	
            }
        }

        $message = addslashes($message);
        $time = date("Y-m-d H:i");


        // ADDED BY Thomas 20.2.2002

       $nom    = addslashes($nom);
       $prenom = addslashes($prenom);

       // END ADDED BY THOMAS

        $sql = "INSERT INTO `$tbl_posts` ".
               "(topic_id, forum_id, poster_id, post_time, poster_ip, nom, prenom)".
               "VALUES ('$topic', '$forum', '".$userdata['user_id']."','$time', '$poster_ip', '$nom', '$prenom')";

        if(!$result = mysql_query($sql, $db))
        {
            error_die("Error - Could not enter data into the database. Please go back and try again");
        }
        
        $this_post = mysql_insert_id();
        if($this_post)
        {
            $sql = "INSERT INTO `$tbl_posts_text` (post_id, post_text) VALUES ($this_post, '$message')";

            if(!$result = mysql_query($sql, $db)) 
            {
                error_die("Could not enter post text!<br>Reason:".mysql_error());
            }
        }

        $sql = "UPDATE `$tbl_topics` ".
               "SET topic_replies = topic_replies+1, ".
               "topic_last_post_id = $this_post, ".
               "topic_time = '$time' ".
               "WHERE topic_id = '$topic'";

        if(!$result = mysql_query($sql, $db))
        {
            error_die("Error - Could not enter data into the database. Please go back and try again");
        }

        if($userdata["user_id"] != -1) 
        {
            $sql = "UPDATE `$tbl_users` SET user_posts=user_posts+1 WHERE (user_id = ".$userdata['user_id'].")";
            $result = mysql_query($sql, $db) OR error_die("Error updating user post count.");
        }

        $sql = "UPDATE `$tbl_forums` ".
               "SET forum_posts = forum_posts+1, forum_last_post_id = '$this_post' ".
               "WHERE forum_id = '$forum'";

        $result = mysql_query($sql, $db) OR error_die("Error updating forums post count.");

        $sql = "SELECT t.topic_notify, u.user_email, u.username, u.user_id 
                FROM `$tbl_topics` t, `$tbl_users` u
                WHERE t.topic_id = '$topic' AND t.topic_poster = u.user_id";

        $result = mysql_query($sql, $db) OR error_die("Couldn't get topic and user information from database.");

        $m = mysql_fetch_array($result,MYSQL_ASSOC);

        // added for claro 1.5 : send notification for user who subscribed for it

        $sql = "SELECT * FROM `$tbl_user_notify` AS notif, $TABLEUSER AS U
                      WHERE notif.topic_id = '$topic'
                      AND  notif.user_id  = U.user_id
                      ";
        $notifyResult = mysql_query($sql);
        $subject = get_syslang_string($sys_lang, "l_notifysubj");

        // send mail to registered user for notification

        while ($list = mysql_fetch_array($notifyResult))
        {

           $user_id = $list['user_id'];
           $message = get_syslang_string($sys_lang,"l_dear")." ".$list['prenom']." ".$list['nom'].",\n";
           $message.= get_syslang_string($sys_lang, "l_notifybody");
           eval("\$message =\"$message\";");
           claro_mail_user($user_id, $message, $subject); //send the e-mail to those claroline users
           //echo "user_id : ".$user_id." has been notified<br>";
        }

        /*  this code is from phpbb 1.4, but not used in claro 1.5.

        if($m["topic_notify"] == 1 && $m["user_id"] != $userdata["user_id"])
        {
            // We have to get the mail body and subject line in the board default language!
            $subject = get_syslang_string($sys_lang, "l_notifysubj");
            $message = get_syslang_string($sys_lang, "l_notifybody");
            eval("\$message =\"$message\";");
            mail($m[user_email], $subject, $message, "From: $email_from\r\nX-Mailer: phpBB $phpbbversion");
        }

        */


        $total_topic = get_total_posts($topic, $db, "topic")-1;  
        // Subtract 1 because we want the nr of replies, not the nr of posts.

        $forward = 1;
     
        echo	"<br>\n",
                "<table border=\"0\" cellpadding=\"1\" cellspacing=\"0\" align=\"center\" valign=\"top\" width=\"$tablewidth\">\n",
                "<tr>\n",
                "<td  bgcolor=\"$table_bgcolor\">\n",
                "<table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\n",
                "<tr bgcolor=\"$color1\">\n",
                "<td>\n",
                "<center>\n",
                $l_stored,"\n",
                "<ul>\n",
                "$l_click <a href=\"viewtopic.php?topic=",$topic,"&forum=",$forum,"\">",$l_here,"</a> ",$l_viewmsg,"\n",
                "<p>\n",
                $l_click ," <a href=\"viewforum.php?forum=$forum\">$l_here</a>\n", "$l_returntopic\n",
                "</ul>\n",
                "</center>\n",
                "</td>\n",
                "</tr>\n",
                "</table>\n",
                "</td>\n",
                "</tr>\n",
                "</table>\n",
                "<br>\n";
    }
    else
    {
        // Private forum logic here.

        if(($forum_type == 1) && !$user_logged_in && !$logging_in)
        {
    ?>
    <form action="<?php echo $PHP_SELF ?>" method="post">
    <table border="0" cellpadding="1" cellspacing="0" align="center" valign="top" width="<?php echo $tablewidth?>">
    <tr>
    <td bgcolor="<?php echo $table_bgcolor ?>">
    <table border="0" cellpadding="1" cellspacing="1" width="100%">
    <tr bgcolor="<?php echo $color1 ?>" align="left">
    <td align="center"><?php echo $l_private?></td>
    </tr>
    <tr bgcolor="<?php echo $color2 ?>" align="left">
    <td align="center">

    </td>
    </tr>
    <tr bgcolor="<?php echo $color1 ?>" align="left">
    <td align="center">
    <input type="hidden" name="forum" value="<?php echo $forum?>">
    <input type="hidden" name="topic" value="<?php echo $topic?>">
    <input type="hidden" name="post" value="<?php echo $post?>">
    <input type="hidden" name="quote" value="<?php echo $quote?>">
    <input type="submit" name="logging_in" value="<?php echo $l_enter?>">
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </form>
    <?php
            require('page_tail.php');
            exit();
        }
        else 
        {
            if ($logging_in)
            {
                if ($username == '' || $password == '') 
                {
                    error_die($l_userpass);
                }
                if (!check_username($username, $db)) 
                {
                    error_die($l_nouser);
                }
                if (!check_user_pw($username, $password, $db)) 
                {
                    error_die($l_wrongpass);
                }
             
                /* if we get here, user has entered a valid username and password combination. */
                $userdata = get_userdata($username, $db);
                $sessid   = new_session($userdata['user_id'], $REMOTE_ADDR, $sesscookietime, $db);	
                set_session_cookie($sessid, $sesscookietime, $sesscookiename, $cookiepath, $cookiedomain, $cookiesecure);
            }

            // ADDED BY CLAROLINE: exclude non identified visitors
            if (!$_uid AND !$fakeUid)
            {
                echo	"<center>",
                        "<p>",
                        $langLoginBeforePost1,"<br>",
                        $langLoginBeforePost2,
                        "<a href=../../index.php>",$langLoginBeforePost3,"</a>",
                        "</p>",
                        "</center>";
                exit();
            }
        
            if ($forum_type == 1)
            {
                // To get here, we have a logged-in user. So, check whether that user is allowed to view
                // this private forum.
                if (!check_priv_forum_auth($userdata['user_id'], $forum, TRUE, $db))
                {
                    error_die("$l_privateforum $l_nopost");
                }
                // Ok, looks like we're good.
            }
        }
        
       
    ?>
    <form action="<?php echo $PHP_SELF?>" method="post">
    <input type="hidden" name="md5" value="<?php echo $md5; ?>">

    <table border="0">

    <tr valign="top">
    <td align="right"><?php echo $l_body?> : 
    <?php
        if($quote)
        {
            if($r = mysql_query("SELECT pt.post_text, p.post_time, u.username 
                                 FROM `$tbl_posts` p, `$tbl_users` u, `$tbl_posts_text` pt 
                                 WHERE p.post_id   = '$post' 
                                 AND   p.poster_id = u.user_id 
                                 AND   pt.post_id  = p.post_id", $db))
            {
                $m                = mysql_fetch_array($r,MYSQL_ASSOC);
                $text             = desmile($m['post_text']);
                $text             = str_replace("<BR>", "\n", $text);
                $text             = stripslashes($text);
                $text             = bbdecode($text);
                $text             = undo_make_clickable($text);
                $text             = str_replace("[addsig]", "", $text);
                $syslang_quotemsg = get_syslang_string($sys_lang, "l_quotemsg");
                eval("\$reply = \"$syslang_quotemsg\";");
            }
            else 
            {
                error_die("Error Contacting database. Please try again.\n<br>$sql");
            }
        }
    ?>
    </td>
    <td>
    <?php claro_disp_html_area('message', $reply) ?>
    </td>
    </tr>

    <tr>
    <td>
    </td>
    <td>
    <input type="hidden" name="forum" value="<?php echo $forum?>">
    <input type="hidden" name="topic" value="<?php echo $topic?>">
    <input type="hidden" name="quote" value="<?php echo $quote?>">
    <input type="submit" name="submit" value="<?php echo $l_submit?>">
    &nbsp;<input type="submit" name="cancel" value="<?php echo $l_cancelpost?>">
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </form>
    <?php     
        // Topic review
        echo    "<br>",
                "<center>",
                "<a href=\"viewtopic.php?topic=$topic&forum=$forum\" target=\"_blank\">",
                "<b>$l_topicreview</b>",
                "</a>",
                "</center>",
                "<br>";

    }
} // end if $is_groupAllowed
else
{
	echo "This is not available for you";
}
require('page_tail.php');
?>
