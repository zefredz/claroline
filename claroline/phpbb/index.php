<?php //     $Id$
/***************************************************************************
                          index.php  -  description
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
$tlabelReq = 'CLFRM___';

include 'functions.php';
include 'config.php';
$pagetitle = $l_indextitle;
$pagetype  = 'index';
include 'page_header.php';

$is_forumAdmin = $is_courseAdmin;

//stats
include $includePath.'/lib/events.lib.inc.php';
event_access_tool($_tid, $_SESSION['_courseTool']['label']);

// GET FORUM CATEGORIES

$sql = "SELECT `c`.`cat_id`, `c`.`cat_title`, `c`.`cat_order`
        FROM   `".$tbl_catagories."` c, `".$tbl_forums."` f
        WHERE `f`.`cat_id` = `c`.`cat_id`
        GROUP BY `c`.`cat_id`, `c`.`cat_title`, `c`.`cat_order`
        ORDER BY `c`.`cat_order` ASC";

$categories       = claro_sql_query_fetch_all($sql);
$total_categories = count($categories);

// GET FORUMS DATA

if( ! $viewcat    ) $viewcat = -1;
if( $viewcat != -1) $limit_forums = 'WHERE f.cat_id = '.$viewcat;
else                $limit_forums = '';

$sql = "SELECT f.*, u.username, u.user_id, p.post_time, g.id gid
        FROM `".$tbl_forums."` f
        LEFT JOIN `".$tbl_posts."` p 
               ON p.post_id = f.forum_last_post_id
        LEFT JOIN `".$tbl_users."` u 
               ON u.user_id = p.poster_id
        LEFT JOIN `".$tbl_student_group."` g 
               ON g.forumId = f.forum_id
          ".$limit_forums."
        ORDER BY f.forum_order, f.cat_id, f.forum_id ";

$forumList = claro_sql_query_fetch_all($sql);


// GET GROUP FORUM IDS OF CURRENT USER 

$sql = "SELECT `g`.`forumId`
        FROM `".$tbl_student_group."` `g`,
             `".$tbl_user_group."` `gu`
        WHERE `g`.`id`    = `gu`.`team`
          AND `gu`.`user` = '".$_uid."'";

$curUserGroupList = claro_sql_query_fetch_all_cols($sql);
$curUserGroupList = $curUserGroupList['forumId'];

// GET FORUM IDS OF THE FORUMS THE CURRENT USER TUTORS

$sql = "SELECT forumId, id groupId 
        FROM `".$tbl_student_group."`
        WHERE tutor = '".$_uid."'";

$tutorGroupList = claro_sql_query_fetch_all_cols($sql);





echo "<table width=\"100%\" class=\"claroTable\">";

for($i = 0; $i < $total_categories; $i++)
{
    if( $viewcat != -1 )
    {
        if( $categories[$i]['cat_id'] != $viewcat)
        {
            $title = stripslashes( $categories[$i]['cat_title'] );

            echo "<tr align=\"left\" valign=\"top\">\n"
                ."<td colspan=\"6\" bgcolor=\"#4171B5\">\n"
                ."<font color=\"white\"><b>".$title."</b></font>\n"
                ."</td>\n"
                ."</tr>\n\n"

                ."<tr class=\"headerX\" align=\"center\">"
                ."<th colspan=\"2\" align=\"left\"><small>".$l_forum."</small></th>\n"
                ."<th><small>".$l_topics  ."</small></th>\n"
                ."<th><small>".$l_posts   ."</small></th>\n"
                ."<th><small>".$l_lastpost."</small></th>\n"
                ."</tr>\n\n";

            continue;
        }
    }

    $title = stripslashes( $categories[$i]['cat_title'] );

    /* ADDED FOR CLAROLINE :distinguish group forums category from others.
     * For now, the group forums category id is '1'. But, this device should 
     * change for something cleaner.
     */

    $goupForumCategory = $categories[$i]['cat_id'] == 1 ? true : false;

    /*
     * CATEGORY BANNER
     */

    echo "<tr align=\"left\" valign=\"top\">\n\n"
        ."<th colspan=\"7\" class=\"superHeader\">\n"
        .$title
        ."</th>\n"
        ."</tr>\n\n"

        ."<tr class=\"headerX\" align=\"center\">"
        ."<th colspan=\"2\" align=\"left\">".$l_forum."</td>"
        ."<th>".$l_topics  ."</th>"
        ."<th>".$l_posts   ."</th>"
        ."<th>".$l_lastpost."</th>"
        ."</tr>\n\n";

    foreach($forumList as $thisForum)
    {
        if( $thisForum['cat_id'] == $categories[$i]['cat_id'] )
        {
            $name         = stripslashes($thisForum['forum_name']);
            $desc         = stripslashes($thisForum['forum_desc']);
            $forumId      = $thisForum['forum_id'    ];
            $total_topics = $thisForum['forum_topics'];
            $total_posts  = $thisForum['forum_posts' ];
            $last_post    = $thisForum['post_time'   ];

            if ( empty($last_post) ) $last_post = $langNoPost;

            echo "<tr align=\"left\" valign=\"top\">\n\n";

            if(    $last_post != 'No Posts'
                && datetime_to_timestamp($last_post) > $last_visit )
            {
                $forumImg = 'red_folder.gif';
            }
            else
            {
                $forumImg = 'folder.gif';
            }

            echo "<td align=\"center\" valign=\"top\" width=\"5%\">\n"
                ."<img src=\"".$clarolineRepositoryWeb."img/".$forumImg."\">\n"
                ."</td>\n";


            echo "<td>\n";


            /* ADDED FOR CLAROLINE : Visit only my group forum if not admin or 
             * tutor.If tutor, see all groups but indicate my groups.
             */

            if($goupForumCategory)
            {
                if (   in_array($forumId, $curUserGroupList)
                    || in_array($forumId, $tutorGroupList['forumId'])
                    || $is_forumAdmin
                    || ! $groupForumPrivate)
                {
                    echo "<a href=\"viewforum.php?gidReq=".$thisForum['gid']
                        ."&forum=".$forumId."\">"
                        .$name
                        ."</a>\n";

                   if ( in_array($forumId, $tutorGroupList['forumId']) )
                   {
                        echo "&nbps;<small>(".$langOneMyGroups.")</small>";
                   }

                   if ( in_array($forumId, $curUserGroupList) )
                   {
                      echo "&nbsp;<small>(".$langMyGroup.")</small>\n";
                   }
                }
                else
                {
                    echo $name;
                }
            }
            else
            {
                echo "<a href=\"viewforum.php?forum=".$forumId."\">"
                    .$name
                    ."</a> ";
            }

            echo "<br><small>".$desc."</small>\n"
                ."</td>\n"

                ."<td width=5% align=\"center\" valign=\"middle\">\n"
                ."<small>".$total_topics."</small>\n"
                ."</td>\n"

                ."<td width=5% align=\"center\" valign=\"middle\">\n"
                ."<small>".$total_posts."<small>\n"
                ."</td>\n"

                ."<td width=15% align=\"center\" valign=\"middle\">\n"
                ."<small>".$last_post."</small>"
                . "</td>\n";

            //$forum_moderators = get_moderators($forumId, $db);

            echo "</tr>\n";
        }
    }
}


echo "</table>\n";

require 'page_tail.php'; // include the claro footer.
?>