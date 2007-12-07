<?php  // $Id$
session_start();

/***************************************************************************
                            editpost.php  -  description
                             -------------------
    begin                : Sat June 17 2000
    copyright            : (C) 2001 The phpBB Group
    email                : support@phpbb.com



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
include('functions.php');
include('config.php');
require('auth.php');
include('page_header.php');


if($is_courseAdmin)
{
	$pagetitle = "Edit Post";
	$pagetype  = "index";

	if($submit)
	{
		/*==========================
		    FORM SUBMIT MANAGEMENT
		  ==========================*/

		$result = mysql_query("SELECT * FROM `$tbl_posts` WHERE post_id = '$post_id'", $db)
		          or die($err_db_retrieve_data);
		          
		if (mysql_num_rows($result) <= 0)   die($err_db_retrieve_data);

		$myrow = mysql_fetch_array($result);

		$poster_id        = $myrow['poster_id'];
		$forum_id         = $myrow['forum_id'];
		$topic_id         = $myrow['topic_id'];
		$this_post_time   = $myrow['post_time'];
		list($day, $time) = split(" ", $myrow['post_time']);
		$posterdata       = get_userdata_from_id($poster_id, $db);
		$date             = date("Y-m-d H:i");

		$is_html_disabled = false;

		if($allow_html == 0 || isset($html) )
		{
			$message = htmlspecialchars($message);
			$is_html_disabled = true;
		}

		$message = addslashes($message);

		if(!$delete)
		{
			/*--------------------------------------
			               POST  UPDATE
	  		  --------------------------------------*/

			$forward = 1;
			$topic   = $topic_id;
			$forum   = $forum_id;

			$result = mysql_query("UPDATE `$tbl_posts_text` SET post_text = '$message' 
								   WHERE (post_id = '$post_id')", $db) 
					or error_die("Unable to update the posting in the database");

			$subject = strip_tags($subject);

			if(isset($subject) && (trim($subject) != ''))
			{
				if(!isset($notify)) $notify = 0;
				else                $notify = 1;

				$subject = addslashes($subject);

				$result = mysql_query("UPDATE `$tbl_topics` 
				                       SET topic_title = '$subject', topic_notify = '$notify' 
									   WHERE topic_id = '$topic_id'", $db) 
						  or error_die("Unable to update the topic subject in the database");
			}
		 
			echo	"<table border=\"0\" cellpadding=\"1\" ",
					"align=\"center\" valign=\"top\" width=\"$tablewidth\">\n",

					"<tr bgcolor=\"$color1\" align=\"left\">\n",
					"<td>\n",

					"<center>\n",
					$l_stored," \n",
					"<ul>\n",
					$l_click," <a href=\"viewtopic.php?topic=$topic_id&forum=$forum_id\">$l_here</a>\n",
					$l_viewmsg,"<P>$l_click <a href=\"viewforum.php?forum=$forum_id\">$l_here</a>\n",
					$l_returntopic,"\n",
					"</ul>\n",
					"</center>\n",

					"</td>\n",
					"</tr>\n",

					"</table>\n";
		}
		else
		{
			/*--------------------------------------
			              POST DELETE
	  		 --------------------------------------*/

			$now_hour         = date("H");
			$now_min          = date("i");
			list($hour, $min) = split(":", $time);

			$last_post_in_thread = get_last_post($topic_id, $db, "time_fix");

			$r = mysql_query("DELETE FROM `$tbl_posts` WHERE post_id = '$post_id'", $db) 
			     or error_die("Couldn't delete post from database");
		
			$r = mysql_query("DELETE FROM `$tbl_posts_text` WHERE post_id = '$post_id'", $db)
			     or error_die("Couldn't delete post from database");

			if($last_post_in_thread == $this_post_time)
			{
				$topic_time_fixed = get_last_post($topic_id, $db, "time_fix");

				$r = mysql_query("UPDATE `$tbl_topics` SET topic_time = '$topic_time_fixed' 
				                  WHERE topic_id = '$topic_id'", $db) 
					 or error_die("Couldn't update to previous post time - last post has been removed");
			}

			if(get_total_posts($topic_id, $db, "topic") == 0) 
			{
				$r = mysql_query("DELETE FROM `$tbl_topics` WHERE topic_id = '$topic_id'", $db) 
				     or error_die("Couldn't delete topic from database");

				$topic_removed = TRUE;
			}

			if($posterdata['user_id'] != -1)
			{
					$r = mysql_query("UPDATE `$tbl_users` SET user_posts = user_posts - 1 
					                  WHERE user_id = '".$posterdata['user_id']."'", $db) 
					     or error_die("Couldn't change user post count.");
			}

			sync($db, $forum, 'forum');

			if(!$topic_removed)
			{
				sync($db, $topic_id, 'topic');
			}

			/* CONFIRMATION MESSAGE */
			
			echo	"<table border=\"0\" cellpadding=\"1\" ",
					"align=\"center\" valign=\"top\" width=\"$tablewidth\">",

					"<tr bgcolor=\"",$color1,"\">",
					"<td>",

					"<center>",

					"<p>",
					$l_deleted,
					"</p>",

					"<p>",
					$l_click," <a href=\"viewforum.php?forum=$forum_id\">",$l_here,"</a> ",
					$l_returntopic,
					"</p>",

					"<p>",
					$l_click," <a href=\"index.php\">",$l_here,"</a>",
					$l_returnindex,
					"</p>",

					"</center>",

					"</td>",
					"</tr>",

					"</table>";
		}													// end post update
		
	}														// end submit management
	else
	{
		/*==========================
		      EDIT FORM BUILDING
		  ==========================*/

		$result = mysql_query("SELECT p.*, pt.post_text,
		                              u.username, u.user_id, u.user_sig, 
		                              t.topic_title, t.topic_notify 
		                       FROM `$tbl_posts` p, `$tbl_users` u, 
		                            `$tbl_topics` t, `$tbl_posts_text` pt,
									`$tbl_forums` f
		                       WHERE (p.post_id = '$post_id')
							   AND (p.topic_id = '$topic')
							   AND (f.forum_id = '$forum')
					           AND (pt.post_id = p.post_id)
		                       AND (p.topic_id = t.topic_id)
							   AND (p.forum_id = f.forum_id)
		                       AND (p.poster_id = u.user_id)", $db) 
		          or	error_die("Couldn't get user and topic information from the database.");

		if(!$myrow = mysql_fetch_array($result))
			error_die("Error - The forum you selected does not exist. Please go back and try again.");

		$message = $myrow['post_text'];

		if(eregi("\[addsig]$", $message)) $addsig = 1;
		else $addsig = 0;

		$message = eregi_replace("\[addsig]$", "\n_________________\n" . $myrow['user_sig'], $message);   
		$message = str_replace("<BR>", "\n", $message);
		$message = stripslashes($message);
		$message = desmile($message);
		$message = bbdecode($message);
		$message = undo_make_clickable($message);
		$message = undo_htmlspecialchars($message);

		// Special handling for </textarea> tags in the message, which can break the editing form..
		$message = preg_replace('#</textarea>#si', '&lt;/TEXTAREA&gt;', $message);

		list($day, $time) = split(" ", $myrow['post_time']);
?>
<form action="<?php echo $PHP_SELF; ?>" method="post">
<table border="0">
<tr valign="top">
<td align="center" colspan="2"><b><?php echo $pagetitle?></b></td>
</tr>
<?php

		$first_post = is_first_post($topic, $post_id, $db);

		if($first_post)
		{
?>
<tr valign="top">
<td align="right">
<label for="subject"><?php echo $l_subject?></label> : 
</td>
<td>
<input type="text" name="subject" id="subject" size="50" maxlength="100" value="<?php echo stripslashes($myrow['topic_title'])?>">
</td>
</tr>
<?php
		}
?>
<tr valign="top">
<td align="right"><?php echo $l_body?> : </td>
<td>
<?php claro_disp_html_area('message', $message); ?>
</td>
</tr>

<tr valign="top">
<td align="right"><label for="delete" ><?php echo $l_delete?></label> : </td>
<td>
<input type="checkbox" name="delete" id="delete"><br>
</td>
</tr>

<tr>
<td>
</td>
<td>
<input type="hidden" name="post_id" value="<?php echo $post_id?>">
<input type="hidden" name="forum" value="<?php echo $forum?>">
<input type="submit" name="submit" value="<?php echo $l_submit?>">
</td>
</tr>
</table>

<br>
<center>
<?php
	echo	"<a href=\"viewtopic.php?topic=$topic&forum=$forum\" target=\"_blank\">",
			"<b>",$l_topicreview,"</b>",
			"</a>";
?>
</center>

<br>
<?php
	} // end else

	include('page_tail.php');

}	// end if is allowed to edit and delete
?>