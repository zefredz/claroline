<?php
/***************************************************************************
                            viewtopic.php  -  description
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

$pagetitle = $l_topictitle;
$pagetype  = 'viewtopic';

$topicSettingList = get_topic_settings($topic); 

$forum            = $topicSettingList['forum_id'];
$topic_subject    = own_stripslashes($topicSettingList['topic_title']);
$lock_state       = $topicSettingList['topic_status'];

$forumSettingList = get_forum_settings($forum, $topic);

$forum_name       = own_stripslashes($forumSettingList['forum_name']);
 

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


include('page_header.'.$phpEx);

if ( ! $start) $start = 0;

$sql = "SELECT p.`post_id`,   p.`topic_id`,  p.`forum_id`,
               p.`poster_id`, p.`post_time`, p.`poster_ip`,
               p.`nom` lastname, p.`prenom` firstname,
               pt.`post_text` 
        FROM `".$tbl_posts."`      p, 
             `".$tbl_posts_text."` pt 
        WHERE topic_id  = '".$topic."' 
          AND p.post_id = pt.`post_id`
        ORDER BY post_id";

require $includePath.'/lib/pager.lib.php';

$postPager = new claro_sql_pager($sql, $start, $posts_per_page);
$postPager->set_pager_call_param_name('start');

$postList  = $postPager->get_result_list();



$pagerUrl = $PHP_SELF."?topic=".$topic;

$postPager->disp_pager_tool_bar($pagerUrl);

echo "<table class=\"claroTable\" width=\"100%\">"
    ."<tr align=\"left\">"
    ."<th class=\"superHeader\">";

/*
 * EMAIL NOTIFICATION COMMANDS
 */

// For (Added for claro 1.5) execute notification preference change 
// if the command was called

if ($cmd && $_uid)
{
    switch ($cmd)
    {
        case 'exNotify' :
            request_topic_notification($_uid, $topic);
            break;

        case 'exdoNotNotify' :
            cancel_topic_notification($_uid, $topic);
            break;
    }

    $increaseTopicView = false; // the notification change command doesn't 
                                // have to be considered as a new topic 
                                // consult
}
else
{
	$increaseTopicView = true;
}




// For (Added for claro 1.5) allow user to be have notification for this 
// topic or disable it
 
if ( isset($_uid) )  //anonymous user do not have this function
{
    echo "<div style=\"float: right;\">\n"
        ."<small>";

    if (is_topic_notification_requested($_uid, $topic))   // display link NOT to be notified
    {
        echo "<img src=\"".$clarolineRepositoryWeb."img/email.gif\">"
            .get_syslang_string($sys_lang, 'l_notify')
            ." [<a href=\"".$PHP_SELF."?topic=".$topic."&cmd=exdoNotNotify\">"
            .$l_disable
            ."</a>]";
    }
    else   //display link to be notified for this topic
    {
        echo  "<a href=\"".$PHP_SELF."?topic=".$topic."&cmd=exNotify\">"
            ."<img src=\"".$clarolineRepositoryWeb."img/email.gif\"> "
            .get_syslang_string($sys_lang, 'l_notify')
            ."</a>";
    }

    echo "</small>\n"
        ."</div>\n";

    } //end not anonymous user

   echo $topic_subject

        ."</th>\n"
        ."</tr>\n";

    foreach($postList as $thisPost )
    {
        // Check if the forum post is after the last login
        // and choose the image according this state

        $post_time = datetime_to_timestamp($thisPost['post_time']);

        if($post_time < $last_visit) $postImg = 'post.gif';
        else                         $postImg = 'postred.gif';

        echo "<tr>\n"

            ."<th class=\"headerX\">\n"
            ."<img src=\"".$clarolineRepositoryWeb."img/".$postImg."\" alt=\"\">"
            .$l_author," : <b>",$thisPost['firstname']." ".$thisPost['lastname']."</b> "
            ."<small>".$l_posted." : ".$thisPost['post_time']."</small>\n"
            ."</th>\n"

            ."</tr>\n"

            ."<tr>\n"

            ."<td>\n"
            .claro_parse_user_text(own_stripslashes($thisPost['post_text']))."\n";

                    // commentedby Thomas 30-11-2001
                    //  echo "<a href=\"".$url_phpbb."/reply.".$phpEx."?topic=".$topic
                    //      ."&forum=".$forum."&post=".$thisPost['post_id']."&quote=1\">"
                    //      .$langQuote
                    //      ."</a>&nbsp;&nbsp;";

        if($is_allowedToEdit)
        {
            echo "<p>\n"

                ."<a href=\"editpost.php"
                ."?post_id=".$thisPost['post_id']."&topic=".$topic."&forum=".$forum."\">"
                ."<img src=\"".$clarolineRepositoryWeb."img/edit.gif\" border=\"0\" alt=\"".$langEditDel."\">"
                ."</a>\n"

                ."<a href=\"editpost.php"
                ."?post_id=".$thisPost['post_id']."&topic=".$topic."&forum=".$forum
                ."&delete=delete&submit=submit\">"
                ."<img src=\"".$clarolineRepositoryWeb."img/delete.gif\" "
                     ."border=\"0\" alt\"".$langEditDel."\">"
                ."</a>\n"

                ."</p>\n";
        }

        echo	"</td>\n",
                "</tr>\n";
    } // end for each

    if ($increaseTopicView)
    {
         $sql = "UPDATE `".$tbl_topics."`
                 SET   topic_views = topic_views + 1
                 WHERE topic_id    = '".$topic."'";

        claro_sql_query($sql);
    }

    echo "</table>\n";

$postPager->disp_pager_tool_bar($PHP_SELF."?topic=".$topic."&forum=".$forum);

require 'page_tail.php';
?>