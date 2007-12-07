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
	header("Location: viewforum.php?forum=$forum");
	exit();
}

include('functions.php');
include('config.php');
require('auth.php');
$pagetitle = "New Topic";
$pagetype = "newtopic";

$userFirstName = $_user['firstName'];
$userLastName  = $_user['lastName' ];

$sql = "
SELECT 	`f`.`forum_name` forum_name,
		`f`.`forum_access` forum_access,
		`f`.`forum_type` forum_type,
		`g`.`id`	`idGroup`,
		`g`.`name` 	`nameGroup`
	FROM `".$tbl_forums."` `f`
	LEFT JOIN `".$tbl_student_group."` `g`
		ON `f`.`forum_id` = `g`.`forumId`
	WHERE `f`.`forum_id` = '".$forum."'";

if(!$result = mysql_query($sql, $db))
	error_die("Can't get forum data.");

if(!$myrow = mysql_fetch_array($result,MYSQL_ASSOC))
	error_die("The forum you are attempting to post to does not exist. Please try again.");

$forum_name 		= $myrow['forum_name'  ];
$forum_access 		= $myrow['forum_access'];
$forum_type 		= $myrow['forum_type'  ];
$forum_groupId 		= $myrow['idGroup'     ];
$forum_groupname	= $myrow['nameGroup'   ];
$forum_id 			= $forum;

// Check if the forum isn't attached to a group, 
// or -- if it is attached --, check the user 
// is allowed to see the current group forum.

if (     is_null($myrow['idGroup']) 
    || ( $myrow['idGroup'] == $_gid && $is_groupAllowed) )
{
	if($submit)
	{
		$subject = strip_tags($subject);
		
		if(trim( strip_tags($message)) == '' || trim($subject) == '')
		{
			error_die($l_emptymsg);
		}
		
		// set as anonymous phpBB user. we try to do it better soon
		// Claroline team
			
		$userdata = array("user_id" => -1);
		
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
		//		}
		// }

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
		
		if(!$smile)
		{
			$message = smile($message);
		}

		$message   = str_replace("\n", "<BR>", $message);
		$message   = str_replace("<w>", "<s><font color=red>", $message);
	    $message   = str_replace("</w>", "</font color></s>", $message);
	    $message   = str_replace("<r>", "<font color=#0000FF>", $message);
	    $message   = str_replace("</r>", "</font color>", $message);
	
		$message   = censor_string($message, $db);
		$message   = addslashes($message);
		$subject   = strip_tags($subject);
		$subject   = censor_string($subject, $db);
		$subject   = addslashes($subject);
		$poster_ip = $REMOTE_ADDR;
		$time      = date('Y-m-d H:i');


		// ADDED BY Thomas 20.2.2002

		$userLastName    = addslashes($userLastName);
		$userFirstName = addslashes($userFirstName);

		// END ADDED BY THOMAS

		// to prevent [addsig] from getting in the way, 
		// let's put the sig insert down here.
		
		if($sig && $userdata["user_id"] != -1)
		{
			$message .= "\n[addsig]";
		}
	
		$sql = "INSERT INTO `".$tbl_topics."` 
		        SET topic_title  = '".$subject."', 
				    topic_poster = '".$userdata['user_id']."', 
				    forum_id     = '".$forum."', 
				    topic_time   = '".$time."', 
				    topic_notify = 1,
			 	    nom          = '".$userLastName."', 
				    prenom       = '".$userFirstName."'";

	$result = mysql_query($sql, $db) 
			  or error_die("Couldn't enter topic in database.");
	
	$topic_id = mysql_insert_id();
	
	$sql = "INSERT INTO `".$tbl_posts."`
			SET topic_id  = '".$topic_id."', 
			    forum_id  = '".$forum."', 
			    poster_id = '".$userdata[user_id]."', 
			    post_time = '".$time."', 
			    poster_ip = '".$poster_ip."', 
			    nom       = '".$userLastName."', 
			    prenom    = '".$userFirstName."'";
	
	$result = mysql_query($sql) 
			  or error_die("Couldn't enter post in database.");

	$post_id = mysql_insert_id();
	
	if($post_id)
	{
		$sql = "INSERT INTO `".$tbl_posts_text."` 
		        SET post_id   = '".$post_id."', 
		            post_text = '".$message."'";
		
		$result = mysql_query($sql) 
		          or error_die("Could not enter post text!");

		$sql = "UPDATE `".$tbl_topics."` 
		        SET   topic_last_post_id = '".$post_id."' 
				WHERE topic_id = '".$topic_id."'";

		$result = mysql_query($sql) 
				  or error_die("Could not update topics table!");
	}


	if($userdata[user_id] != -1)
	{
		$sql = "UPDATE `$tbl_users` 
		        SET user_posts=user_posts+1 
		        WHERE user_id = '".$userdata['user_id']."'";
		
		$result = mysql_query($sql) 
				  or error_die("Couldn't update users post count.");
	}
	
	$sql = "UPDATE `".$tbl_forums."` 
            SET   forum_posts        = forum_posts+1, 
                  forum_topics       = forum_topics+1, 
                  forum_last_post_id = '".$post_id."' 
            WHERE forum_id           = '".$forum."'";

	$result = mysql_query($sql, $db) 
			  or error_die("Couldn't update forums post count.");
			  
	$topic       = $topic_id;
	$total_topic = get_total_posts($topic, $db, "topic")-1;
	
	// Subtract 1 because we want the nr of replies, not the nr of posts.
	$forward = 1;
	
	include('page_header.php');
	
	echo	"<br>",
	
			"<table border=\"0\" cellpadding=\"1\" cellspacing=\"0\" ",
			"align=\"center\" valign=\"top\" width=\"$tablewidth\">",
			
			"<tr align=\"left\">",
			"<td>",
			"<center>",
			$l_stored,
			"<p>",$l_click,
			" <a href=\"viewtopic.$phpEx?topic=$topic_id&forum=$forum\">",
			$l_here,
			"</a> ",
			$l_viewmsg,"<p>",
			$l_click,
			" <a href=\"viewforum.$phpEx?forum=$forum_id\">",
			$l_here,
			"</a> ", 
			$l_returntopic,
			"</center>",
			"</td>",
			"</tr>",
			
			"</table>";
			
	} // end if submit
	else
	{
		include('page_header.php');
	
		// ADDED BY CLAROLINE: exclude non identified visitors
		if (!$_uid AND !$fakeUid)
		{
			die("<center><br><br><font face=\"arial, helvetica\" size=2>$langLoginBeforePost1<br>
				$langLoginBeforePost2<a href=../../index.php>$langLoginBeforePost3.</a></center>");
		}
		// END ADDED BY CLAROLINE exclude visitors unidentified
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
}
else
{
	header("Location: index.php");
	exit();
}
require('page_tail.php');
?>
