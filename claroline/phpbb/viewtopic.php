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
include('extention.inc');
include('functions.'.$phpEx);
include('config.'.$phpEx);
require('auth.'.$phpEx);
$pagetitle = $l_topictitle;
$pagetype = "viewtopic";

$sql = "
SELECT 	`f`.`forum_name` forum_name,
		`f`.`forum_access` forum_access,
		`f`.`forum_type` forum_type,
		`g`.`id`	`idGroup`

FROM `".$tbl_forums."` `f`,
     `".$tbl_topics."` t 

# Check possible attached group ...

LEFT JOIN `".$tbl_student_group."` `g`
ON `f`.`forum_id` = `g`.`forumId`

WHERE `f`.`forum_id` = '".$forum."'
AND   t.topic_id = '".$topic."'
AND   t.forum_id = f.forum_id    ";

$result = mysql_query($sql) 
          or error_die("An Error Occured<hr>Could not connect to the forums database.");

$myrow = mysql_fetch_array($result)
         or error_die("Error - The forum/topic you selected does not exist. Please go back and try again.");

if (     is_null($myrow['idGroup']) // there is no group attached to this forum
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

        $sql = "SELECT topic_title, topic_status FROM `$tbl_topics` 
        		WHERE topic_id = '$topic'";

        $total = get_total_posts($topic, $db, "topic");

        if($total > $posts_per_page)
        {
            $times = 0;
            for($x = 0; $x < $total; $x += $posts_per_page)
            $times++;
            $pages = $times;
        }

        $result = mysql_query($sql, $db) 
                  or error_die("<big>An Error Occured<big><hr>Could not connect to the forums database.");

        $myrow = mysql_fetch_array($result);
        $topic_subject = own_stripslashes($myrow['topic_title']);
        $lock_state = $myrow['topic_status'];

        include('page_header.'.$phpEx);


        if($total > $posts_per_page)
        {
            echo "<table>";
            $times = 1;
            echo "<tr align=\"left\"><td>",$l_gotopage," ( ";
            $last_page = $start - $posts_per_page;

            if($start > 0)
            {
                echo "<a href=\"$PHP_SELF?topic=$topic&forum=$forum&start=$last_page\">",$l_prevpage,"</a> ";
            }

            for($x = 0; $x < $total; $x += $posts_per_page)
            {
                if($times != 1)
                echo " | ";

                if($start && ($start == $x))
                {
                    echo $times;
                }

                elseif($start == 0 && $x == 0)
                {
                    echo "1";
                }
                else
                {
                    echo "<a href=\"$PHP_SELF?mode=viewtopic&topic=$topic&forum=$forum&start=$x\">",$times,"</a>\n";
                }

                $times++;
            }

            if(($start + $posts_per_page) < $total)
            {
                $next_page = $start + $posts_per_page;
                echo "<a href=\"$PHP_SELF?topic=$topic&forum=$forum&start=$next_page\">",$l_nextpage,"</a>\n";
            }

            echo	" )\n",
                    "</td>\n",
                    "</tr>\n",
                    "</table>\n";
        }
    ?>

    <table class="claroTable" width="100%">
    <tr align="left">
    <th class="superHeader">
    <?php 
	
    /*
     * EMAIL NOTIFICATION COMMANDS
     */

    // For (Added for claro 1.5) execute notification preference change if the command was called

    switch ($cmd)
    {
            case "exNotify" :
                  $sql = "INSERT INTO `$tbl_user_notify`
                                 (`user_id`, `topic_id`)
                                 VALUES ('$_uid', '$topic')
                          ";
                  mysql_query($sql);
                  $notifyChange = true;
                  break;

            case "exdoNotNotify" :
                  $sql = "DELETE FROM `$tbl_user_notify`
                                            WHERE topic_id = '$topic'
                                            AND user_id='$_uid'
                                            ";
                  mysql_query($sql);
                  $notifyChange = true;
                  break;
    }

    // For (Added for claro 1.5) allow user to be have notification for this topic or disable it
     
   	if (isset($_uid))  //anonymous user do not have this function
    {
      //see in DB if user is notified or not

      $sql = "SELECT *
                      FROM `$tbl_user_notify`
                      WHERE topic_id = '$topic'
                      AND user_id='$_uid'
              ";
      $result = mysql_query($sql);

      //echo $sql."<br>";
      if ( mysql_num_rows($result) ) {$userInNotifyMode = true;}

      // add appropriate link

      echo "<div style=\"float: right;\">"
          ."<small>";

      if ($userInNotifyMode)   // display link NOT to be notified
      {
        echo "<img src=\"".$clarolineRepositoryWeb."img/email.gif\">"
        	.get_syslang_string($sys_lang, "l_notify")
            ." [<a href=\"$PHP_SELF?mode=viewtopic&topic=$topic&forum=$forum&cmd=exdoNotNotify\">"
            .$l_disable
            ."</a>]";
       }
       else   //display link to be notified for this topic
       {
       echo  "<a href=\"$PHP_SELF?mode=viewtopic&topic=$topic&forum=$forum&cmd=exNotify\">"
            ."<img src=\"".$clarolineRepositoryWeb."img/email.gif\">"
            ."</a>"
            ."<a href=\"$PHP_SELF?mode=viewtopic&topic=$topic&forum=$forum&cmd=exNotify\">"
            .get_syslang_string($sys_lang, "l_notify")
            ."</a>";
       }

       echo  "</small>"
            ."</div>";

     }//end not anonymous user

   	echo $topic_subject;


    ?>
    </th>

    </tr>

    <?php

        if(isset($start))
        {
            $sql = "SELECT p.*, pt.post_text FROM `".$tbl_posts."` p, `".$tbl_posts_text."` pt 
                    WHERE topic_id = '".$topic."' 
                    AND p.post_id = pt.post_id
                    ORDER BY post_id LIMIT ".$start.", ".$posts_per_page;
        }
        else
        {
            $sql = "SELECT p.*, pt.post_text 
                    FROM `".$tbl_posts."` p, `".$tbl_posts_text."` pt
                    WHERE topic_id = '".$topic."'
                    AND p.post_id = pt.post_id
                    ORDER BY post_id LIMIT ".$posts_per_page;
        }

        $result = mysql_query($sql, $db) or error_die("<big>An Error Occured</big><hr>Could not connect to the Posts database. $sql");

        $myrow = mysql_fetch_array($result);

        $count = 0;

    do
    {
        // Check if the forum post is after the last login
        // and choose the image according this state
        list($post_date, $post_time) = split(' ', $myrow['post_time']);
		list($year, $month, $day)    = explode('-', $post_date);
		list($hour, $min)            = explode(':', $post_time);
		$post_time                   = mktime($hour, $min, 0, $month, $day, $year);

        if($post_time < $last_visit) $postImg = 'post.gif';
        else                         $postImg = 'postred.gif';

        
        echo	"<tr>\n",
                "<th class=\"headerX\">\n",
                "<img src=\"".$clarolineRepositoryWeb."img/".$postImg."\" alt=\"\">",
                $l_author," : <b>",$myrow['prenom']," ",$myrow['nom'],"</b>",
                " <small>",$l_posted," : ",$myrow['post_time'],"</small>\n",
                "</td>\n",
                "</tr>\n";

        $message = own_stripslashes($myrow['post_text']);

        // Before we insert the sig, we have to strip its HTML if HTML is disabled by the admin.
        // We do this _before_ bbencode(), otherwise we'd kill the bbcode's html.

        echo	"<tr>\n",
                "<td>\n",
                claro_parse_user_text($message),"\n";

    // Added by Thomas 30-11-2001
    // echo "<a href=\"$url_phpbb/reply.$phpEx?topic=$topic&forum=$forum&post=$myrow[post_id]&quote=1\">$langQuote</a>&nbsp;&nbsp;";

        if($is_allowedToEdit)
        {
            echo	"<p>\n",
	    
                    "<a href=\"$url_phpbb/editpost.$phpEx?post_id=".$myrow['post_id']."&topic=$topic&forum=$forum\">",
                    "<img src=\"".$clarolineRepositoryWeb."img/edit.gif\" border=\"0\" alt=\"",$langEditDel,"\">",
		    "</a>\n",
		    
		    "<a href=\"$url_phpbb/editpost.$phpEx?post_id=".$myrow['post_id']."&topic=$topic&forum=$forum&delete=delete&submit=submit\">",
                    "<img src=\"".$clarolineRepositoryWeb."img/delete.gif\" border=\"0\" alt\"",$langEditDel,"\">",
                    "</a>\n",
		    
                    "</p>\n";
        }

        echo	"</td>\n",
                "</tr>\n";

       $count++;

    } while($myrow = mysql_fetch_array($result)); // do while

        if ($notifyChange != true)
        {
             $sql = "UPDATE `$tbl_topics`
                    SET topic_views = topic_views + 1
                    WHERE topic_id = '$topic'";

            @mysql_query($sql, $db);
        }
    ?>

    </table>

    </td>

    </tr>

    <?php

        if($total > $posts_per_page)
        {
            $times = 1;

            echo	"<tr align=\"right\">",
                    "<td colspan=2>\n",
                    $l_gotopage," ( ";

            $last_page = $start - $posts_per_page;

            if($start > 0)
            {
                echo "<a href=\"",$PHP_SELF,"?topic=",$topic,"&forum=",$forum,"&start=",$last_page,"\">",$l_prevpage,"</a> ";
            }

            for($x = 0; $x < $total; $x += $posts_per_page)
            {
                if($times != 1)
                {
                    echo " | ";
                }

                if($start && ($start == $x))
                {
                     echo $times;
                }
                elseif($start == 0 && $x == 0)
                {
                    echo "1";
                }
                else
                {
                    echo "<a href=\"$PHP_SELF?mode=viewtopic&topic=$topic&forum=$forum&start=$x\">",$times,"</a>";
                }

                $times++;
            }

            if(($start + $posts_per_page) < $total)
            {
                $next_page = $start + $posts_per_page;

                echo "<a href=\"",$PHP_SELF,"?topic=",$topic,"&forum=",$forum,"&start=",$next_page,"\">",$l_nextpage,"</a>";
            }

            echo	"</td>\n",
                    "</tr>\n";
        }

    ?>


    </td>
    <?
    }
} // end if $is_groupAllowed
else
{
	echo "This is not available for you";
}
require('page_tail.php');
?>