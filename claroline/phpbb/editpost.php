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

require 'functions.php';
require 'config.php';
require 'page_header.php';


if($is_courseAdmin)
{
	$pagetitle = "Edit Post";
	$pagetype  = "index";

	if($submit)
	{
		/*==========================
		    FORM SUBMIT MANAGEMENT
		  ==========================*/
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

		if($allow_html == 0 || isset($html)) $message = htmlspecialchars($message);

		$message = addslashes($message);

		if( ! $delete)
		{
			/*--------------------------------------
			               POST  UPDATE
	  		  --------------------------------------*/

			$forward = 1;
			$topic   = $topic_id;
			$forum   = $forum_id;

            update_post($post_id, $message, $subject);

            disp_confirmation_message ($l_stored, $forum_id, $topic_id);
		}
		else
		{
			/*--------------------------------------
			              POST DELETE
	  		 --------------------------------------*/

            delete_post($post_id, $topic_id, $forum, $posterdata['user_id']);

			/* CONFIRMATION MESSAGE */

            disp_confirmation_message ($l_deleted, $forum_id);

		}													// end post update
		
	}														// end submit management
	else
	{
		/*==========================
		      EDIT FORM BUILDING
		  ==========================*/

        $sql = "SELECT p.post_id, p.topic_id, p.forum_id, p.poster_id, 
                       p.post_time, p.poster_ip, p.nom , p.prenom,
                       pt.post_text,
		               u.username, u.user_id, u.user_sig, 
		               t.topic_title, t.topic_notify
                       
		        FROM `".$tbl_posts."` p, `".$tbl_users."` u, 
		             `".$tbl_topics."` t, `".$tbl_posts_text."` pt,
				     `".$tbl_forums."` f

		        WHERE p.post_id   = '".$post_id."'
                  AND p.topic_id  = '".$topic."'
                  AND f.forum_id  = '".$forum."'
                  AND pt.post_id  = p.post_id
                  AND p.topic_id  = t.topic_id
                  AND p.forum_id  = f.forum_id
                  AND p.poster_id = u.user_id";

		$myrow = claro_sql_query_fetch_all($sql);
        
        if (count($myrow) == 1) $myrow = $myrow[0];
        else error_die ('unexisting forum');

		$message = stripslashes($myrow['post_text']);
		// Special handling for </textarea> tags in the message, which can break the editing form..
		$message = preg_replace('#</textarea>#si', '&lt;/TEXTAREA&gt;', $message);

		list($day, $time) = split(' ', $myrow['post_time']);
?>
<form action="<?php echo $PHP_SELF; ?>" method="post">
<table border="0">
<tr valign="top">
<td colspan="2"><b><?php echo $pagetitle?></b></td>
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
	echo "<a href=\"viewtopic.php?topic=".$topic."&forum=".$forum."\" target=\"_blank\">"
		."<b>".$l_topicreview."</b>"
		."</a>";
?>
</center>

<br>
<?php
	} // end else

	include('page_tail.php');

}	// end if is allowed to edit and delete
?>