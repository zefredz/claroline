<?php
session_start();

/***************************************************************************
                            viewforum.php  -  description
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
include('functions.php');
include('config.php');
require('auth.php');

$pagetitle = $l_viewforum;
$pagetype = "viewforum";
if($forum == -1) header("Location: $url_phpbb");

$sql = "SELECT 	`f`.`forum_type`,
        `f`.`forum_name`,
        `g`.`id`	`idGroup`,
        `g`.`name` 	`nameGroup`
        FROM `".$tbl_forums."` `f`
        LEFT JOIN `".$tbl_student_group."` `g`
        ON `f`.`forum_id` = `g`.`forumId`
        WHERE `f`.`forum_id` = '".$forum."'";

if(!$result = mysql_query($sql)) 
	error_die("An Error Occured<hr>Could not connect to the forums database.");
if(!$myrow = mysql_fetch_array($result,MYSQL_ASSOC))
	error_die("Error - The forum you selected does not exist. Please go back and try again.");

// Check if the forum isn't attached to a group, 
// or -- if it is attached --, check the user 
// is allowed to see the current group forum.

if (     is_null($myrow['idGroup']) 
    || ( $myrow['idGroup'] == $_gid && $is_groupAllowed) )
{
	$forum_name = own_stripslashes($myrow['forum_name']);

	// Note: page_header is included later on, because this page might need to send a cookie.

//	if(($myrow['forum_type'] == 1) && !$user_logged_in && !$logging_in)
//	{
//								....
//
//		   There were previously an	authentication form	propriatary	to phpBB ...
//	}
//	else

	{
		require('page_header.php');

		if ($myrow["forum_type"] == 1)
		{
			// To get here, we have a logged-in user. So, check whether that user is allowed to view
			// this private forum.

			if (!check_priv_forum_auth($userdata['user_id'], $forum, FALSE, $db))
			{
				error_die($l_privateforum." ".$l_noread);
			}

			// Ok, looks like we're good.
		}

	?>

<table class="claroTable" border="0" cellpadding="1" cellspacing="1" width="100%">

<tr class="superHeader">
<th colspan="6"><?php echo $forum_name ?></th>
</tr>

<tr class="headerX" align="left">
<th colspan="2">&nbsp;<?php echo $l_topic?></td>
<th width="9%" align="center"><?php echo $l_replies?></td>
<th width="20%" align="center">&nbsp;<?php echo $l_poster?></td>
<th width="8%" align="center"><?php echo $langSeen?></td>
<th width="15%" align="center"><?php echo $langLastMsg?></td>
</tr>

	<?php
	if(!$start) $start = 0;

	$sql = "SELECT t.*, u.username, u2.username as last_poster, p.post_time
			FROM `$tbl_topics` t
			LEFT JOIN `$tbl_users` u ON t.topic_poster = u.user_id
			LEFT JOIN `$tbl_posts` p ON t.topic_last_post_id = p.post_id
			LEFT JOIN `$tbl_users` u2 ON p.poster_id = u2.user_id
			WHERE t.forum_id = '$forum'
			ORDER BY topic_time DESC LIMIT $start, $topics_per_page";

	$result = mysql_query($sql, $db)
		or error_die("</table></table>An Error Occured<hr>phpBB could not query the topics database.<br>$sql");

	$topics_start = $start;

	if($myrow = mysql_fetch_array($result,MYSQL_ASSOC))
	{
		do
		{
			echo"\n<tr>";

			$replys             = $myrow["topic_replies"];
			$last_post          = $myrow["post_time"];
			$last_post_datetime = $myrow["post_time"];

			//list($last_post_datetime, $null) = split("by", $last_post);
			list($last_post_date, $last_post_time) = split(" ", $last_post_datetime);
			list($year, $month, $day)              = explode("-", $last_post_date);
			list($hour, $min)                      = explode(":", $last_post_time);
			$last_post_time                        = mktime($hour, $min, 0, $month, $day, $year);

			if( $replys >= $hot_threshold)
			{
				if($last_post_time < $last_visit) $image = $hot_folder_image;
				else                              $image = $hot_newposts_image;
			}
			else
			{
				// Original phpBB statements
				//if($last_post_time < $last_visit) $image = $folder_image;
				//else                              $image = $newposts_image;

				// Claroline statements
				if($last_post_time < $last_visit)
				{
					$image = $clarolineRepositoryWeb."img/forum.gif";
					$alt="";
				}
				else
				{
					$image = $clarolineRepositoryWeb."img/red_forum.gif";
					$alt="";
				}
			}

			if($myrow[topic_status] == 1)         $image = $locked_image;

			echo	"<td><img src=\"".$image."\" alt=\"".$alt."\"></td>\n";

			$topic_title = own_stripslashes($myrow['topic_title']);
			$pagination = '';
			$start = '';
			$topiclink = "viewtopic.".$phpEx."?topic=".$myrow['topic_id']."&forum=".$forum;

			if ( $replys+1 > $posts_per_page)
			{
				$pagination .= "&nbsp;&nbsp;&nbsp;(<img src=\"".$posticon."\">".$l_gotopage." ";
				$pagenr      = 1;
				$skippages   = 0;

				for($x = 0; $x < $replys + 1; $x += $posts_per_page)
				{
					$lastpage = (($x + $posts_per_page) >= $replys + 1);

					if($lastpage)
					{
						$start = "&start=$x&$replys";
					}
					else
					{
						if ($x != 0)
						{
							$start = "&start=$x";
						}

						$start .= "&" . ($x + $posts_per_page - 1);
					}

					if($pagenr > 3 && $skippages != 1)
					{
						$pagination .= ", ... ";
						$skippages = 1;
					}

					if ($skippages != 1 || $lastpage)
					{
						if ($x!=0) $pagination .= ", ";
						$pagination .= "<a href=\"".$topiclink.$start."\">".$pagenr."</a>";
					}

					$pagenr++;
				}
				$pagination .= ")";
			}

			$topiclink .= "&$replys";

			echo	"<td>\n",
					"&nbsp;",
					"<a href=\"",$topiclink,"\">",$topic_title,"</a>",$pagination,"\n",
					"</td>\n";

			echo	"<td align=\"center\"><small>",$replys,"</small></td>\n",
					"<td align=\"center\"><small>",$myrow["prenom"]," ",$myrow[nom],"<small></td>\n",
					"<td align=\"center\"><small>",$myrow["topic_views"],"<small></td>\n",
					"<td align=\"center\"><small>",$last_post,"<small></td>\n",
					"</tr>\n";
		}
		while($myrow = mysql_fetch_array($result,MYSQL_ASSOC));
	}
	else
	{
		echo "<td bgcolor=\"$color1\" colspan =\"6\" align=\"center\">",$l_notopics,"</td></tr>\n";
	}
	?>
	</table>
	<?php
	}

	/*--------------------------------------
					TOPICS PAGER
			(When there are to much topics 
				   for a single page)
	  --------------------------------------*/

	$sql = "SELECT count(*) AS total FROM `$tbl_topics` WHERE forum_id = '$forum'";

	$r = mysql_query($sql) or error_die("Error could not contact the database!");

	list($all_topics) = mysql_fetch_array($r);

	$count = 1;

	$next = $topics_start + $topics_per_page;

	if($all_topics > $topics_per_page)
	{
		if($next < $all_topics)
		{
			echo	"<p align=\"right\">",
					"<small>",
					"<a href=\"viewforum.php?forum=",$forum,"&start=",$next,"&gidReq=",$_gid,">",
					$l_nextpage,
					"</a> | ";

			for($x = 0; $x < $all_topics; $x++)
			{
				if(!($x % $topics_per_page))
				{
					if($x == $topics_start)
					{
						echo "$count\n";
					}
					else
					{
						echo	"<a href=\"viewforum.php?forum=",$forum,"&start=",$x,"&gidReq=",$_gid,">",
								$count,
								"</a>\n";
					}

					$count++;

					if(!($count % 10)) echo "</small></p>\n";
				}
			} // end if ! $x % $topics_per_page
			 
		} // end if $next < all_topics
		
	} // end if $all_topics > $topics_per_page
	
} // end if $is_groupAllowed
else
{
	echo "This is not available for you";
}
require('page_tail.php');
?>
