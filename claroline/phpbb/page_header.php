<?php // $Id$
session_register("forumId");

/***************************************************************************
                          page_header.php  -  description
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

/* Who's Online Hack */
$IP = $REMOTE_ADDR;

if($pagetype == "index")
{
	$users_online = get_whosonline($IP, $userdata[username], 0, $db);
}
if($pagetype == "viewforum" || $pagetype == "viewtopic")
{
	$users_online = get_whosonline($IP, $userdata[username], $forum, $db);
}
if($pagetype == "admin")
{
	$header_image = "../$header_image";
}


$login_logout_link = make_login_logout_link($user_logged_in, $url_phpbb);

$langFile = "phpbb";
// suspect langfile call -- need to be checked in further release (The good one is supposed to be in config.php (Hugues june 3 2004).


$is_allowedToEdit = $is_courseAdmin || $is_platformAdmin;
$is_forumAdmin = $is_courseAdmin || $is_platformAdmin;



$nameTools = $l_forums;

$noPHP_SELF = true; //because  phpBB need always param IN URL

include('../inc/claro_init_header.inc.php');

if ( ! $is_courseAllowed)
	claro_disp_auth_form();


/*
echo "<a href=\"./search.php?addterms=any&forum=all&sortby=p.post_time%20desc&searchboth=both&submit=Rechercher\">$langLastMsgs</a>";
*/

if($is_forumAdmin)
{

claro_disp_tool_title($l_forums, 
                      $is_allowedToEdit ? 'help_forum.php' : false);
}	// end if prof or assistant


/*================================================
  RELATE TO GROUP DOCUMENT AND SPACE FOR CLAROLINE
  ================================================*/

// Determine if Forums are private. O=public, 1=private

$forumPriv = mysql_query("SELECT private FROM `$tbl_group_properties`") or die('Error in file '.__FILE__.' at line '.__LINE__);

while ($myForumPriv = mysql_fetch_array($forumPriv))
{
	$privProp = $myForumPriv['private'];
}


// Determine if uid is tutor for this course

$sqlTutor = mysql_query("SELECT tutor FROM cours_user
                         WHERE user_id=\"".$_uid."\"
                         AND code_cours=\"".$_cid."\"") or die('Error in file '.__FILE__.' at line '.__LINE__);

while ($myTutor = mysql_fetch_array($sqlTutor))
{
	$tutorCheck = $myTutor['tutor'];
}


// Determine if forum category is Groups

$forumCatId = mysql_query("SELECT cat_id FROM `$tbl_forums`
                           WHERE forum_id=\"".$forum."\"") or die('Error in file '.__FILE__.' at line '.__LINE__);

while ($myForumCat = mysql_fetch_array($forumCatId))
{
	$catId = $myForumCat['cat_id'];
}


// Check which group and which forum user is a member of

$findTeamUser = mysql_query("SELECT team, forumId, tutor, secretDirectory
                             FROM  `$tbl_student_group` s, `$tbl_user_group` u
                             WHERE u.user=\"".$_uid."\"
                             AND   s.id = u.team") or die('Error in file '.__FILE__.' at line '.__LINE__);

while ($myTeamUser = mysql_fetch_array($findTeamUser))
{
	$myTeam            = $myTeamUser['team'           ];
	$myGroupForum      = $myTeamUser['forumId'        ];
	$myTutor           = $myTeamUser['tutor'          ];
	$secretDirectory   = $myTeamUser['secretDirectory'];
}


// Show Group Documents and Group Space
// only if in Category 2 = Group Forums Category

if (($catId==1) AND ($forum==$myGroupForum))
{
	// group space links

	echo	"<br>\n",
			"<br>\n",
			"<a href=\"../group/group_space.php?gidReq=",$_gid,"\">",
			$langGroupSpaceLink,
			"</a>\n",
			"&nbsp;&nbsp",
			"<a href=\"../group/document.php?gidReq=",$_gid,"\">",
			$langGroupDocumentsLink,
			"</a>\n",
			"<br>\n",
			"<br>\n";
}

/*========================================================================*/


if ($user_logged_in)
{
	// do PM notification.
	$last_visit_date = date("Y-m-d h:i", $last_visit);

	$username = addslashes($userdata[username]);

	$sql = "SELECT count(*) AS count
			FROM `$tbl_priv_msgs` p, `$tbl_users` u
			WHERE p.to_userid = u.user_id and p.msg_status = '0' and u.username = '$username'";

	if(!$result = mysql_query($sql, $db))
	{
		error_die("phpBB was unable to check private messages because " .mysql_error($db));
	}

	$row = @mysql_fetch_array($result);
	$new_message = $row[count];
	$word = ($new_message > 1) ? "messages" : "message";
	$privmsg_url = "$url_phpbb/viewpmsg.$phpEx";

	if ($new_message != 0)
	{
		eval($l_privnotify);
		print $privnotify;
	}
}

/*----------------------------------------
             BREADCRUMB TRAIL
 --------------------------------------*/


switch($pagetype)
{
	case 'index':

		$total_posts = get_total_posts("0", $db, "all");
		$total_users = get_total_posts("0", $db, "users");
		$sql = "SELECT username, user_id FROM `$tbl_users` WHERE user_level != -1 ORDER BY user_id DESC LIMIT 1";
		$res = mysql_query($sql, $db) or die('Error in file '.__FILE__.' at line '.__LINE__);
		$row = mysql_fetch_array($res);
		$newest_user = $row["username"];
		$newest_user_id = $row["user_id"];
		$profile_url = $url_phpbb."/bb_profile.".$phpEx."?mode=view&user=".$newest_user_id;
		$online_url = $url_phpbb."/whosonline.".$phpEx;

		eval($l_statsblock);
		// print $statsblock;
		// print_login_status($user_logged_in, $userdata[username], $url_phpbb);   // deactivated by CLAROLINE

		break;

	case 'viewforum':

		//echo "<h4>",$forum_name,"</h4>";

	case 'viewtopic':

		echo "<small>\n";

		echo	"<a href=\"",$url_phpbb,"/index.",$phpEx,"\">",
				$sitename," Forum Index",
				"</a>",
				$l_separator,
				"<a href=\"",$url_phpbb,"/viewforum.",$phpEx,"?forum=",$forum,"&gidReq=",$_gid,"\">",
				stripslashes($forum_name),
				"</a>";

		if($pagetype != "viewforum")
		{
			echo $l_separator;
		}

		echo $topic_subject;

	echo "</small>\n";

		break;
}

/*----------------------------------------
                 TOOL BAR
 --------------------------------------*/


// go to administration panel

if($is_forumAdmin)
{
	$toolBar [] =	"<a href=../forum_admin/forum_admin.php>".$langAdm."</a>";
}


switch($pagetype)
{
	// 'index' is covered by default

	case 'newtopic':

		$toolBar [] =	$langBackTo.
						"<a href=\"".$url_phpbb."/viewforum.".$phpEx."?forum=".$forum."&gidReq=".$_gid."\">".
						$forum_name.
						"</a>\n";
		break;

	case 'viewforum':

		$toolBar [] =	"<a href=\"newtopic.php?forum=".$forum."&gidReq=".$_gid."\">".$langNewTopic."</a>";

		break;

	case 'viewtopic':

		if($lock_state != 1)
		{
			$toolBar [] =	"<a href=\"$url_phpbb/reply.php?topic=".$topic."&forum=".$forum."&gidReq=".$_gid."\">".
							$langAnswer.
							"</a>\n";
		}
		else
		{
			$toolBar [] =	"<img src=\"".$reply_locked_image."\" border=\"0\">\n";
		}

		$toolBar [] =	"<a href=\"newtopic.php?forum=".$forum."&gidReq=".$_gid."\">".$langNewTopic."</a>";

		break;

	// 'Register' is covered by default

	default:
		break;
}

if (is_array($toolBar)) $toolBar = implode(" | ", $toolBar);


echo "<p align=\"right\">",$toolBar,"<p>";


?>
