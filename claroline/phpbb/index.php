<?php //     $Id$
session_start();
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

include 'extention.inc';
include 'functions.php';
include 'config.php';
require 'auth.php';
$pagetitle = $l_indextitle;
$pagetype = 'index';
include 'page_header.php';

$is_forumAdmin = $is_courseAdmin || $is_platformAdmin;

//stats
include $includePath.'/lib/events.lib.inc.php';
event_access_tool($nameTools);

$sql = "SELECT `c`.`cat_id`, `c`.`cat_title`, `c`.`cat_order`
        FROM   `".$tbl_catagories."` c, `".$tbl_forums."` f
        WHERE `f`.`cat_id` = `c`.`cat_id`
        GROUP BY `c`.`cat_id`, `c`.`cat_title`, `c`.`cat_order`
        ORDER BY `c`.`cat_order` ASC";

$categories       = claro_sql_query_fetch_all($sql);
$total_categories = count($categories);


$sql ="SELECT `g`.`forumId`
       FROM `".$tbl_student_group."` `g`,
           `".$tbl_user_group."` `gu`
       WHERE `g`.`id`    = `gu`.`team`
         AND `gu`.`user` = '".$_uid."'";

$curUserGroupList = claro_sql_query_fetch_all_cols($sql);
$curUserGroupList = $curUserGroupList['forumId'];

echo "<table width=\"100%\" class=\"claroTable\">";


if($total_categories)
{
    if( ! $viewcat ) $viewcat = -1;

    if( $viewcat != -1) $limit_forums = 'WHERE f.cat_id = '.$viewcat;
    else                $limit_forums = '';

    $sql_f = "SELECT f.*, u.username, u.user_id, p.post_time, g.id gid
              FROM `".$tbl_forums."` f
              LEFT JOIN `".$tbl_posts."` p 
                     ON p.post_id = f.forum_last_post_id
              LEFT JOIN `".$tbl_users."` u 
                     ON u.user_id = p.poster_id
              LEFT JOIN `".$tbl_student_group."` g 
                     ON g.forumId = f.forum_id
              ".$limit_forums."
              ORDER BY f.forum_order, f.cat_id, f.forum_id ";

    $forum_row = claro_sql_query_fetch_all($sql_f);

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
                    ."<th><small>".$l_topics."</small></th>\n"
                    ."<th><small>".$l_posts."</small></th>\n"
                    ."<th><small>".$l_lastpost."</small></th>\n"
                    ."</tr>\n\n";

                continue;
            }
        }

        $title = stripslashes( $categories[$i]['cat_title'] );

        /*
         * Added by Thomas for Claroline :
         * distinguish group forums from others
         */

        $catNum = $categories[$i]['cat_id'];

        /* category title */

        echo "<tr align=\"left\" valign=\"top\">\n\n"
            ."<th colspan=\"7\" class=\"superHeader\">\n"
            .$title
            ."</th>\n"
            ."</tr>\n\n"

            ."<tr class=\"headerX\" align=\"center\">"
            ."<th colspan=\"2\" align=\"left\">".$l_forum."</td>"
            ."<th>".$l_topics."</th>"
            ."<th>".$l_posts."</th>"
            ."<th>".$l_lastpost."</th>"
            ."</tr>\n\n";

		@reset($forum_row);

		for($x = 0; $x < count($forum_row); $x++)
		{
			unset($last_post);

			if( $forum_row[$x]['cat_id'] == $categories[$i]['cat_id'] )
			{
				if($forum_row[$x]['post_time'])
				{
					$last_post = $forum_row[$x]['post_time']; // post time format  datetime de mysql
				}

				$last_post_datetime = $forum_row[$x]['post_time'];
                $last_post_time     = datetime_to_timestamp($last_post_datetime);

				// $last_post_time  mktime du champs  post_time.
				if ( empty($last_post) ) $last_post = $langNoPost;

				echo "<tr  align=\"left\" valign=\"top\">\n\n";

				if( $last_post_time > $last_visit && $last_post != 'No Posts' )
				{
					echo	"<td align=\"center\" valign=\"top\" width=5%>\n",
							"<img src=\"".$clarolineRepositoryWeb."img/red_folder.gif\">\n";
							"</td>\n";
				}
				else
				{
					echo	"<td align=\"center\" valign=\"top\" width=\"5%\">\n",
							"<img src=\"".$clarolineRepositoryWeb."img/folder.gif\">\n",
							"</td>\n";
				}

				$name         = stripslashes($forum_row[$x]['forum_name']);
				$total_posts  = $forum_row[$x]['forum_posts'];
				$total_topics = $forum_row[$x]['forum_topics'];
				$desc         = stripslashes($forum_row[$x]['forum_desc']);

				echo	"<td>\n";

				$forum = $forum_row[$x]['forum_id'];

				/*
				 * Claroline feature added by Thomas July 2002
				 * Visit only my group forum if not admin or tutor
				 * If tutor, see all groups but indicate my groups
				 */


				/*--------------------------------------
				              TUTOR VIEW
				  --------------------------------------*/

				if($tutorCheck == 1)
				{
                    $sql = "SELECT forumId, id groupId 
                            FROM `".$tbl_student_group."`
                            WHERE tutor = '".$_uid."'";

                    $tutorGroupList = claro_sql_query_fetch_all_cols($sql);

                    if ( in_array($forum, $tutorGroupList['forumId']) )
                    {
                        echo "<a href=\"viewforum.php?gidReq=",$forum_row[$x]['gid']
                            ."&forum=".$forum_row[$x]['forum_id']
                            ."&".$total_posts."\">"
                            .$name
                            ."</a>\n"
                            ."&nbsp;(".$langOneMyGroups.")";
                    }
                    else
                    {
                        echo "<a href=\"viewforum.php?gidReq=".$forum_row[$x]['gid']
                            ."&forum=".$forum_row[$x]['forum_id']
                            ."&",$total_posts,"\">"
                            .$name
                            ."</a>\n";
                    }
                }


				/*--------------------------------------
				               ADMIN VIEW
				  --------------------------------------*/

                elseif($is_forumAdmin)
                {
                    echo "<a href=\"viewforum.php?gidReq=",$forum_row[$x]["gid"]
                        ."&forum=",$forum_row[$x]['forum_id']
                        ."&",$total_posts,"\">"
                        .$name
                        ."</a>\n";
                }


                /*--------------------------------------
                              STUDENT VIEW
                  --------------------------------------*/

                elseif($catNum == 1)
                {
                    if (in_array($forum, $curUserGroupList)) // this  cond  must change.
                    {
                        echo "<a href=\"viewforum.php?gidReq=",$forum_row[$x]["gid"]
                            ."&forum=",$forum_row[$x]["forum_id"]
                            ."&$total_posts\">"
                            .$name
                            ."</a>\n"
                            ."&nbsp;&nbsp;(",$langMyGroup,")\n";
                    }
                    else
                    {
                        if($privProp == 1)
                        {
                            echo $name;
                        }
                        else
                        {
                            echo "<a href=\"viewforum.php?gidReq=",$forum_row[$x]['gid']
                                ."&forum=",$forum_row[$x]['forum_id']
                                ."&$total_posts\">"
                                .$name
                                ."</a>\n";
                        }
                    }
                }
                else // OTHER FORUMS ...
                {
                    echo "<a href=\"viewforum.php?gidReq=",$forum_row[$x]['gid']
                        ."&forum=",$forum_row[$x]['forum_id']
                        ."&$total_posts\">"
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

				$forum_moderators = get_moderators($forum_row[$x]['forum_id'], $db);

                echo "</tr>\n";
			}
		}
	}
}


echo "</table>";

require('page_tail.php'); // include the claro footer.
?>