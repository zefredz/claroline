<?php
session_start(); 
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

include 'extention.inc';
include 'functions.'.$phpEx;
include 'config.'.$phpEx;
require 'auth.'.$phpEx;

$pagetitle = $l_topictitle;
$pagetype  = 'viewtopic';

$sql = "SELECT 	`f`.`forum_name`   `forum_name`,
                `f`.`forum_access` `forum_access`,
                `f`.`forum_type`   `forum_type`,
                `g`.`id`           `idGroup`

        FROM `".$tbl_forums."` `f`,
             `".$tbl_topics."` t 

        # Check possible attached group ...
        LEFT JOIN `".$tbl_student_group."` `g`
               ON `f`.`forum_id` = `g`.`forumId`

        WHERE `f`.`forum_id` = '".$forum."'
         AND  `t`.`topic_id` = '".$topic."'
         AND  `t`.`forum_id` = f.forum_id    ";

$forumSettingList = claro_sql_query_fetch_all($sql);

if ( count($forumSettingList) == 1) $forumSettingList = $forumSettingList[0];
else                                error_die('Unexisting forum.');


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

$forum_name = own_stripslashes($forumSettingList['forum_name']);

$total      = get_total_posts($topic, $db, 'topic');

if($total > $posts_per_page)
{
    $times = 0;
    for($x = 0; $x < $total; $x += $posts_per_page) $times++;
    $pages = $times;
}

$sql = "SELECT topic_title, topic_status 
        FROM `".$tbl_topics."` 
        WHERE topic_id = '".$topic."'";

$topicSettingList = claro_sql_query_fetch_all($sql);
if ( count($topicSettingList) == 1) $topicSettingList = $topicSettingList[0];
else                                error_die('Unexisting topic.');


$topic_subject    = own_stripslashes($topicSettingList['topic_title']);

$lock_state       = $topicSettingList['topic_status'];

include('page_header.'.$phpEx);


if($total > $posts_per_page)
{
    echo "<table>\n";

    $times = 1;

    echo "<tr align=\"left\">\n"
        ."<td>\n"
        .$l_gotopage." ( ";

    $last_page = $start - $posts_per_page;

    if($start > 0)
    {
        echo "<a href=\"".$PHP_SELF."?topic=".$topic."&forum=".$forum
                                   ."&start=".$last_page."\">"
            .$l_prevpage
            ."</a> ";
    }

    for($x = 0; $x < $total; $x += $posts_per_page)
    {
        if($times != 1) echo " | ";

        if    ($start && ($start == $x)) echo $times;
        elseif($start == 0 && $x == 0)   echo "1";
        else
        {
            echo "<a href=\"".$PHP_SELF."?mode=viewtopic"
                ."&topic=".$topic."&forum=".$forum."&start=".$x."\">"
                .$times
                ."</a>\n";
        }

        $times++;
    } // end for($x = 0; $x < $total; $x += $posts_per_page)

    if(($start + $posts_per_page) < $total)
    {
        $next_page = $start + $posts_per_page;

        echo "<a href=\"".$PHP_SELF."?topic=".$topic."&forum=".$forum
                                   ."&start=$next_page\">"
            .$l_nextpage
            ."</a>\n";
    }

    echo " )\n"
        ."</td>\n"
        ."</tr>\n"
        ."</table>\n";
}



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

                  $sql = "INSERT INTO `$tbl_user_notify`
                          SET `user_id`  = '".$_uid."',
                              `topic_id` = '".$topic."'";

                  claro_sql_query($sql);
                  $notifyChange = true;
                  break;

            case 'exdoNotNotify' :
                  $sql = "DELETE FROM `$tbl_user_notify`
                          WHERE topic_id = '".$topic."'
                            AND user_id  = '".$_uid."'";

                  claro_sql_query($sql);
                  $notifyChange = true;
                  break;
    }
}

// For (Added for claro 1.5) allow user to be have notification for this 
// topic or disable it
 
if (isset($_uid))  //anonymous user do not have this function
{
    //see in DB if user is notified or not
   
    $sql = "SELECT COUNT(*) 
            FROM `".$tbl_user_notify."`
            WHERE `topic_id` = '".$topic."'
              AND `user_id`  ='".$_uid."'";

    $userInNotifyMode = claro_sql_query_get_single_value($sql);

    // add appropriate link

    echo "<div style=\"float: right;\">\n"
        ."<small>";

    if ($userInNotifyMode)   // display link NOT to be notified
    {
        echo "<img src=\"".$clarolineRepositoryWeb."img/email.gif\">"
            .get_syslang_string($sys_lang, 'l_notify')
            ." [<a href=\"".$PHP_SELF."?mode=viewtopic"
            ."&topic=".$topic."&forum=".$forum."&cmd=exdoNotNotify\">"
            .$l_disable
            ."</a>]";
    }
    else   //display link to be notified for this topic
    {
        echo  "<a href=\"".$PHP_SELF."?mode=viewtopic"
            ."&topic=".$topic."&forum=".$forum."&cmd=exNotify\">"
            ."<img src=\"".$clarolineRepositoryWeb."img/email.gif\">"
            ."</a>"

            ."<a href=\"".$PHP_SELF."?mode=viewtopic"
            ."&topic=".$topic."&forum=".$forum."&cmd=exNotify\">"
            .get_syslang_string($sys_lang, 'l_notify')
            ."</a>";
    }

    echo  "</small>\n"
        ."</div>\n";

    } //end not anonymous user

   echo $topic_subject

        ."</th>\n"
        ."</tr>\n";

    if ( ! $start) $start = 0;

    $sql = "SELECT p.`post_id`,   p.`topic_id`,  p.`forum_id`,
                   p.`poster_id`, p.`post_time`, p.`poster_ip`,
                   p.`nom` lastname, p.`prenom` firstname,
                   pt.`post_text` 
            FROM `".$tbl_posts."`      p, 
                 `".$tbl_posts_text."` pt 
            WHERE topic_id  = '".$topic."' 
              AND p.post_id = pt.`post_id`
            ORDER BY post_id 
            LIMIT ".$start.", ".$posts_per_page;

    $postList = claro_sql_query_fetch_all($sql, $db);

    $count = 0;

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
            .own_stripslashes($thisPost['post_text'])."\n";

                    // Added by Thomas 30-11-2001
                    //  echo "<a href=\"".$url_phpbb."/reply.".$phpEx."?topic=".$topic
                    //      ."&forum=".$forum."&post=".$thisPost['post_id']."&quote=1\">"
                    //      .$langQuote
                    //      ."</a>&nbsp;&nbsp;";

        if($is_allowedToEdit)
        {
            echo "<p>\n"

                ."<a href=\"".$url_phpbb."/editpost.".$phpEx
                ."?post_id=".$thisPost['post_id']."&topic=".$topic."&forum=".$forum."\">"
                ."<img src=\"".$clarolineRepositoryWeb."img/edit.gif\" border=\"0\" alt=\"".$langEditDel."\">"
                ."</a>\n"

                ."<a href=\"".$url_phpbb."/editpost.".$phpEx
                ."?post_id=".$thisPost['post_id']."&topic=".$topic."&forum=".$forum
                ."&delete=delete&submit=submit\">"
                ."<img src=\"".$clarolineRepositoryWeb."img/delete.gif\" "
                     ."border=\"0\" alt\"".$langEditDel."\">"
                ."</a>\n"

                ."</p>\n";
        }

        echo	"</td>\n",
                "</tr>\n";

       $count++;

    } // end for each

    if ($notifyChange != true)
    {
         $sql = "UPDATE `".$tbl_topics."`
                 SET   topic_views = topic_views + 1
                 WHERE topic_id    = '".$topic."'";

        claro_sql_query($sql);
    }

    echo "</table>\n"
        ."</td>\n"
        ."</tr>\n";

if($total > $posts_per_page)
{
    $times = 1;

    echo	"<tr align=\"right\">\n",
            "<td colspan=2>\n",
            $l_gotopage," ( ";

    $last_page = $start - $posts_per_page;

    if($start > 0)
    {
        echo "<a href=\"".$PHP_SELF."?topic=".$topic."&forum=".$forum
                                   ."&start=".$last_page."\">"
            .$l_prevpage
            ."</a>\n";
    }

    for($x = 0; $x < $total; $x += $posts_per_page)
    {
        if($times != 1) echo " | ";

        if    ($start && ($start == $x)) echo $times;
        elseif($start == 0 && $x == 0)   echo '1';
        else
        {
            echo "<a href=\"".$PHP_SELF."?mode=viewtopic"
                ."&topic=".$topic."&forum=".$forum."&start=".$x."\">"
                .$times
                ."</a>\n";
        }

        $times++;
    }

    if(($start + $posts_per_page) < $total)
    {
        $next_page = $start + $posts_per_page;

        echo "<a href=\"".$PHP_SELF."?topic=".$topic."&forum=".$forum
                                   ."&start=".$next_page."\">"
            .$l_nextpage
            ."</a>\n";
    }

    echo	"</td>\n",
            "</tr>\n";

} // end if($total > $posts_per_page)

echo "</td>\n";

require 'page_tail.php';
?>