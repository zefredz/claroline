<?php

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
require 'functions.php';
require 'config.php';

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

require 'page_header.php';

$sql = "SELECT    t.*, p.post_time
        FROM      `".$tbl_topics."` t
        LEFT JOIN `".$tbl_posts."` p 
               ON t.topic_last_post_id = p.post_id
        WHERE     t.forum_id = '".$forum."'
        ORDER BY  topic_time DESC";

if ( ! $start) $start = 0;

require $includePath.'/lib/pager.lib.php';

$topicPager = new claro_sql_pager($sql, $start, $topics_per_page);
$topicPager->set_pager_call_param_name('start');
$topicList  = $topicPager->get_result_list();

$pagerUrl = 'viewforum.php?forum='.$forum.'&gidReq='.$_gid;

$topicPager->disp_pager_tool_bar($pagerUrl);

echo "<table class=\"claroTable\" border=\"0\""
    .      " cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">"

    ."<tr class=\"superHeader\">"
    ."<th colspan=\"6\">".$forum_name."</th>\n"
    ."</tr>"

    ."<tr class=\"headerX\" align=\"left\">\n"
    ."<th colspan=\"2\">&nbsp;". $l_topic."</th>\n"
    ."<th width=\"9%\"  align=\"center\">".$l_replies."</th>\n"
    ."<th width=\"20%\" align=\"center\">&nbsp;".$l_poster."</th>\n"
    ."<th width=\"8%\"  align=\"center\">".$langSeen."</th>\n"
    ."<th width=\"15%\" align=\"center\">".$langLastMsg."</th>\n"
    ."</tr>\n";

$topics_start = $start;

if ( count($topicList) == 0)
{
    echo "<tr>\n" 
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
            $alt   = 'new post';
        }

        if($thisTopic['topic_status'] == 1) $image = $locked_image;

        echo "<td><img src=\"".$image."\" alt=\"".$alt."\"></td>\n";

        $topic_title = own_stripslashes($thisTopic['topic_title']);
        $topic_link  = 'viewtopic.php?topic='.$thisTopic['topic_id'];

        echo "<td>\n"
            ."&nbsp;"
            ."<a href=\"".$topic_link."\">".$topic_title."</a>&nbsp;&nbsp;";

            disp_mini_pager($topic_link, 'start', $replys+1, $posts_per_page);

        echo "</td>\n"

            ."<td align=\"center\"><small>".$replys."</small></td>\n"
            ."<td align=\"center\"><small>".$thisTopic['prenom']." ".$thisTopic['nom']."<small></td>\n"
            ."<td align=\"center\"><small>".$thisTopic['topic_views']."<small></td>\n"
            ."<td align=\"center\"><small>".$last_post."<small></td>\n"

            ."</tr>\n";
}

echo "</table>";

$topicPager->disp_pager_tool_bar($pagerUrl);
require 'page_tail.php';


?>