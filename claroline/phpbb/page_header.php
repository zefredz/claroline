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

if($pagetype == 'index')
{
	$users_online = get_whosonline($IP, $userdata['username'], 0, $db);
}
if($pagetype == 'viewforum' || $pagetype == "viewtopic")
{
	$users_online = get_whosonline($IP, $userdata['username'], $forum, $db);
}


// suspect langfile call -- need to be checked in further release (The good one is supposed to be in config.php (Hugues june 3 2004).

if ( ! $is_courseAllowed) claro_disp_auth_form();

claro_set_display_mode_available(true);

$nameTools = $langForums;

include('../inc/claro_init_header.inc.php');

$is_allowedToEdit = claro_is_allowed_to_edit() 
                    || ( $is_groupTutor && !$is_courseAdmin);
                    // ( $is_groupTutor 
                    //  is added to give admin status to tutor 
                    // && !$is_courseAdmin)
                    // is added  to let course admin, tutor of current group, use student mode
                     
$is_forumAdmin    = claro_is_allowed_to_edit();



//$noPHP_SELF = true; //because  phpBB need always param IN URL


claro_disp_tool_title($langForums, 
                      $is_allowedToEdit ? 'help_forum.php' : false);


/*================================================
  RELATE TO GROUP DOCUMENT AND SPACE FOR CLAROLINE
  ================================================*/

// Determine if Forums are private. O=public, 1=private

$sql = "SELECT private 
        FROM `".$tbl_group_properties."`";

$privProp = claro_sql_query_get_single_value($sql);

// Determine if uid is tutor for this course

$sql = "SELECT tutor 
        FROM `". $tbl_course_user . "`
        WHERE  user_id    ='".$_uid."'
        AND    code_cours ='".$_cid."'";

$sqlTutor = claro_sql_query($sql);

while ($myTutor = mysql_fetch_array($sqlTutor))
{
    $tutorCheck = $myTutor['tutor'];
}

// Determine if forum category is Groups


$sqlForumCatId = "SELECT cat_id FROM `".$tbl_forums."`
                           WHERE forum_id = '".$forum."'";

$forumCatId = claro_sql_query($sqlForumCatId);

while ($myForumCat = mysql_fetch_array($forumCatId))
{
	$catId = $myForumCat['cat_id'];
}


// Check which group and which forum user is a member of

$sqlFindTeamUser = "SELECT team, forumId, tutor, secretDirectory
                 FROM  `".$tbl_student_group."` s, `".$tbl_user_group."` u
                 WHERE u.user=\"".$_uid."\"
                 AND   s.id = u.team";

$findTeamUser = claro_sql_query($sqlFindTeamUser);

while ($myTeamUser = mysql_fetch_array($findTeamUser))
{
	$myTeam          = $myTeamUser['team'           ];
	$myGroupForum    = $myTeamUser['forumId'        ];
	$myTutor         = $myTeamUser['tutor'          ];
	$secretDirectory = $myTeamUser['secretDirectory'];
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
			"<a href=\"../document/document.php?gidReq=",$_gid,"\">",
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

	$username = addslashes($userdata['username']);

	$sql = "SELECT COUNT(*) AS count
			FROM `".$tbl_priv_msgs."` p, 
                 `".$tbl_users."` u
			WHERE p.to_userid = u.user_id 
              AND p.msg_status = '0' 
              AND u.username = '".$username."'";

    $newMsgCount = claro_sql_query_get_single_value($sql);
	$word = ($newMsgCount > 1) ? 'messages' : 'message';
	$privmsg_url = "viewpmsg.php";

	if ($new_message != 0)
	{
		printf($l_privnotify,$new_message,$privmsg_url);
	}
}

/*----------------------------------------
                 TOOL BAR
 --------------------------------------*/


// go to administration panel

if($is_forumAdmin)
{
   if ( isset($catId) && $catId>0 ) $toAdd = '?forumgo=yes&amp;cat_id=' . $catId;
   $toolBar[] = '<a class="claroCmd" href="../forum_admin/forum_admin.php' . $toAdd . '">'
              . '<img src="'.$imgRepositoryWeb.'settings.gif"> '
              . $langAdm
              . '</a>'."\n"
              ;
}

switch($pagetype)
{
	// 'index' is covered by default

	case 'newtopic':

		$toolBar [] = $langBackTo
					. '<a class="claroCmd" href="'.$url_phpbb.'/viewforum.'.$phpEx.'?forum='.$forum.'&amp;gidReq='.$_gid.'">'
					. $forum_name
					. '</a>'."\n";
		break;

	case 'viewforum':

		$toolBar [] =	"<a class=\"claroCmd\" href=\"newtopic.php?forum=".$forum."&amp;gidReq=".$_gid."\">"
                       ."<img src=\"".$imgRepositoryWeb."topic.gif\"> "
                       .$langNewTopic
                       ."</a>";

		break;

	case 'viewtopic':

		$toolBar [] =	"<a class=\"claroCmd\" href=\"newtopic.php?forum=".$forum."&amp;gidReq=".$_gid."\">"
                       ."<img src=\"".$imgRepositoryWeb."topic.gif\"> "
                       .$langNewTopic
                       ."</a>";

		if($lock_state != 1)
		{
			$toolBar [] =	"<a class=\"claroCmd\" href=\"$url_phpbb/reply.php?topic=".$topic."&amp;forum=".$forum."&amp;gidReq=".$_gid."\">"
            ."<img src=\"".$imgRepositoryWeb."reply.gif\"> "
							.$langReply
							."</a>\n";
		}
		else
		{
			$toolBar [] =	"<img src=\"".$reply_locked_image."\" border=\"0\">\n";
		}


		break;

	// 'Register' is covered by default

	default:
		break;
}

if (is_array($toolBar)) $toolBar = implode(' | ', $toolBar);


echo '<p>'.$toolBar.'<p>'."\n";

/*----------------------------------------
             BREADCRUMB TRAIL
 --------------------------------------*/


switch($pagetype)
{
	case 'index':

        // noop ...

		break;

	case 'viewforum':

		//echo "<h4>",$forum_name,"</h4>";

	case 'viewtopic':

		echo "<small>\n";

		echo "<a href=\"",$url_phpbb,"/index.",$phpEx,"\">"
			.$sitename," Forum Index"
			."</a> "
			.$l_separator
			." <a href=\"".$url_phpbb."/viewforum.".$phpEx."?forum=".$forum."&amp;gidReq=".$_gid."\">"
			.stripslashes($forum_name)
			."</a>";

		if($pagetype != "viewforum") echo ' '.$l_separator.' ';

		echo $topic_subject;

	echo "</small>\n";

		break;
}

?>
