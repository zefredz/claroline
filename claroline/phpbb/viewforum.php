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
include 'extention.inc';
include 'functions.php';
include 'config.php';
require 'auth.php';

$pagetitle = $l_viewforum;
$pagetype = 'viewforum';

if($forum == -1) header('Location: '.$url_phpbb);

/* 
 * GET FORUM SETTINGS
 */
$forumSettingList = get_forum_settings($forum);

/* 
 * Check if the forum isn't attached to a group,  or -- if it is attached --, 
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

//  Previous authentication system proper to phpBB
//  if ($forumSettingList['forum_type'] == 1)
//  {
//     if ( ! check_priv_forum_auth($userdata['user_id'], $forum, false, $db))



$forum_name = own_stripslashes($forumSettingList['forum_name']);

/*
 * GET TOPIC LIST
 */

if ( ! $start) $start = 0;

$sql = "SELECT t.*, u.username, u2.username as last_poster, p.post_time
        FROM `".$tbl_topics."` t
        LEFT JOIN `".$tbl_users."` u 
               ON t.topic_poster = u.user_id
        LEFT JOIN `".$tbl_posts."` p 
               ON t.topic_last_post_id = p.post_id
        LEFT JOIN `".$tbl_users."` u2 
               ON p.poster_id = u2.user_id
        WHERE t.forum_id = '".$forum."'
        ORDER BY topic_time DESC 
        LIMIT ".$start.", ".$topics_per_page;

$topicList = claro_sql_query_fetch_all($sql);


require('page_header.php');

echo "<table class=\"claroTable\" border=\"0\""
    .      " cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">"

    ."<tr class=\"superHeader\">"
    ."<th colspan=\"6\">".$forum_name."</th>\n"
    ."</tr>"

    ."<tr class=\"headerX\" align=\"left\">\n"
    ."<th colspan=\"2\">&nbsp;". $l_topic."</th>\n"
    ."<th width=\"9%\" align=\"center\">".$l_replies."</th>\n"
    ."<th width=\"20%\" align=\"center\">&nbsp;".$l_poster."</th>\n"
    ."<th width=\"8%\" align=\"center\">".$langSeen."</th>\n"
    ."<th width=\"15%\" align=\"center\">".$langLastMsg."</th>\n"
    ."</tr>\n";

$topics_start = $start;

if ( count($topicList) == 0)
{
    echo "<tr>" 
        ."<td colspan =\"6\" align=\"center\">".$l_notopics."</td>\n"
        ."</tr>\n";
}
else foreach($topicList as $thisTopic)
{
        echo "<tr>\n";

        $replys             = $thisTopic['topic_replies'];
        $last_post          = $thisTopic['post_time'    ];
        $last_post_datetime = $thisTopic['post_time'    ];

        $last_post_time = datetime_to_timestamp( $last_post_datetime );

        if($last_post_time < $last_visit)
        {
            $image = $clarolineRepositoryWeb.'img/forum.gif';
            $alt='';
        }
        else
        {
            $image = $clarolineRepositoryWeb.'img/red_forum.gif';
            $alt   = '';
        }

        if($thisTopic['topic_status'] == 1) $image = $locked_image;

        echo "<td><img src=\"".$image."\" alt=\"".$alt."\"></td>\n";

        $topic_title = own_stripslashes($thisTopic['topic_title']);
        $pagination  = '';
        $start       = '';
        $topiclink   = "viewtopic.".$phpEx."?topic=".$thisTopic['topic_id']."&forum=".$forum;

        if ( $replys+1 > $posts_per_page)
        {
            $pagination .= "&nbsp;&nbsp;&nbsp;(<img src=\"".$posticon."\">".$l_gotopage." ";
            $pagenr      = 1;
            $skippages   = 0;

            for($x = 0; $x < $replys + 1; $x += $posts_per_page)
            {
                $lastpage = ( ($x + $posts_per_page) >= $replys + 1 );

                if($lastpage)
                {
                    $start = "&start=".$x."&".$replys;
                }
                else
                {
                    if ($x != 0) $start = "&start=".$x;
                    $start .= "&" . ($x + $posts_per_page - 1);
                }

                if($pagenr > 3 && $skippages != 1)
                {
                    $pagination .= ", ... ";
                    $skippages = 1;
                }

                if ($skippages != 1 || $lastpage)
                {
                    if ($x != 0) $pagination .= ', ';
                    $pagination .= "<a href=\"".$topiclink.$start."\">".$pagenr."</a>";
                }

                $pagenr++;
            }

            $pagination .= ")";
        }

        $topiclink .= "&".$replys;

        echo "<td>\n"
            ."&nbsp;"
            ."<a href=\"".$topiclink."\">".$topic_title."</a>".$pagination."\n"
            ."</td>\n"

            ."<td align=\"center\"><small>".$replys."</small></td>\n"
            ."<td align=\"center\"><small>".$thisTopic['prenom']." ".$thisTopic['nom']."<small></td>\n"
            ."<td align=\"center\"><small>".$thisTopic['topic_views']."<small></td>\n"
            ."<td align=\"center\"><small>".$last_post."<small></td>\n"

            ."</tr>\n";
}

echo "</table>";

    /*------------------------------------------------------------------------
        TOPICS PAGER (When there are to much topics  for a single page)
      ------------------------------------------------------------------------*/

    $sql = "SELECT COUNT(*) AS total 
            FROM `".$tbl_topics."` 
            WHERE forum_id = '".$forum."'";

    $all_topics = claro_sql_query_get_single_value($sql);

    $count = 1;

    $next = $topics_start + $topics_per_page;

    if($all_topics > $topics_per_page)
    {
        if($next < $all_topics)
        {
            echo "<p align=\"right\">"
                ."<small>"
                ."<a href=\"viewforum.php?forum=".$forum."&start=".$next."&gidReq=".$_gid.">"
                .$l_nextpage
                ."</a> | ";

            for($x = 0; $x < $all_topics; $x++)
            {
                if( ! ($x % $topics_per_page) )
                {
                    if($x == $topics_start)
                    {
                        echo $count."\n";
                    }
                    else
                    {
                        echo    "<a href=\"viewforum.php?forum=",$forum,"&start=",$x,"&gidReq=",$_gid,">",
                                $count,
                                "</a>\n";
                    }

                    $count++;

                    if( ! ($count % 10) ) echo "</small></p>\n";
                }
            } // end if ! $x % $topics_per_page
             
        } // end if $next < all_topics
        
    } // end if $all_topics > $topics_per_page
    
require 'page_tail.php';
?>