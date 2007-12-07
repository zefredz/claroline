<?php //     $Id$
/***************************************************************************
                           functions.php  -  description
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

$phpEx = "php"; // php extension ....

// Most PHP package has increase the error reporting. 
// The line below set the error reporting to the most fitting one for Claroline

// Most PHP package has increase the error reporting. 
// The line below set the error reporting to the most fitting one for Claroline
// Actually this operation is also done into claro_init_global.inc.php
// But this file is loaded to late in the forum scripts ...
//error_reporting(error_reporting() & ~ E_NOTICE);




// DEPRECATED FUNCTIONS

function is_banned($ipuser, $type, $db) { return false; }
function undo_make_clickable($text)     { return $text; }
function smile($message) { return $message; }
function desmile($message) {return $message;}
function login_form() { error_die('should display the PHPBB login form ...'); }
function check_username($username, $db) {return false;}
function get_userdata($username, $db) {return array("error" => "1");}
function validate_username($username, $db) {return 0;}

function get_userdata_from_id($userId)
{
    global $tbl_users;

    $sql = "SELECT prenom first_name, 
                   nom    last_name, 
                   email, 
                   user_id
            FROM `".$tbl_users."`
            WHERE user_id ='".(int)$userId."'";

    $result = claro_sql_query_fetch_all($sql);

    if ( count($result) == 1 ) return $result[0];
    else                      return false;
}


/**
 * Gets the total number of topics in a form
 */
function get_total_topics($forum_id)
{
	global $tbl_topics;

	$sql = "SELECT COUNT(*) AS total
	        FROM `".$tbl_topics."`
	        WHERE forum_id = '".$forum_id."'";

    return claro_sql_query_get_single_value($sql);
}


/**
 * Used to keep track of all the people viewing the forum at this time
 * Anyone who's been on the board within the last 300 seconds will be
 * returned. Any data older then 300 seconds will be removed
 */
function get_whosonline($IP, $username, $forum, $db)
{
	global $sys_lang, $tbl_whosonline, $l_guest;

	if($username == '') $username = $l_guest;

	$time       = explode(' ', microtime());
	$userusec   = (double)$time[0];
	$usersec    = (double)$time[1];
	$username   = addslashes($username);

    $sql = "DELETE FROM `".$tbl_whosonline."` 
            WHERE date < ".$usersec." - 300";

	$deleteuser = claro_sql_query($sql);

    $sql ="SELECT COUNT(*)
           FROM `".$tbl_whosonline."` 
           WHERE IP = '".$IP."'";

	$userlog    = claro_sql_query_get_single_value($sql);

	if($userlog == 0)
	{
		$sql = "INSERT INTO `".$tbl_whosonline."`
                SET ID = '".$User_Id."',
                    IP = '".$IP."',
                    DATE = '".$usersec."',
                    username = '".$username."',
                    forum = '".$forum."'";

        claro_sql_query($sql);

	}

    $sql = "SELECT COUNT(*) AS total 
            FROM `".$tbl_whosonline."`";

	$resultlogtab   = claro_sql_query($sql);
	$numberlogtab   = mysql_fetch_array($resultlogtab);
	return($numberlogtab['total']);
}

/**
 * Returns the total number of posts in the whole system, a forum, or a topic
 * Also can return the number of users on the system.
 */

function get_total_posts($id, $type)
{
    global $tbl_users, $tbl_posts;

    switch($type)
    {
        case 'users': $condition = "poster_id = '".$id."'";
            break;
        case 'forum': $condition = "forum_id = '".$id."'";
            break;
        case 'topic': $condition = "topic_id = '".$id."'";           
            break;
        case 'all'  : $condition = '1'; // forces TRUE in all cases ...
            break;

        // Old, we should never get this.
        default     : $condition = false;
                      error_die('No type argument in get_total_post().');
    }

    if ($condition)
    {
        $sql = "SELECT COUNT(*) AS total 
                        FROM `".$tbl_posts."` 
                WHERE ".$condition;

        return claro_sql_query_get_single_value($sql);
    }
    else
    {
    	return false;
    }
}

/**
 * Returns the most recent post in a forum, or a topic
 */
function get_last_post($id, $type)
{
    global $l_error, $l_noposts, $l_by, $tbl_posts, $tbl_users ;

    switch($type)
    {
        case 'forum': $condition = "forum_id = '".$id."'";
        break;
        case 'topic': $condition = "topic_id = '".$id."'";
        break;
        case 'user' : $condition = "poster_id = '".$id."'";
        break;
        default : error_die("wrong type argument in get_last_post() function");
    }

    $sql = "SELECT post_time
            FROM `".$tbl_posts."`
            WHERE ".$condition."
            ORDER BY post_time DESC LIMIT 1";

    $result = claro_sql_query_fetch_all($sql);

    if (count($result) == 0) return($l_noposts);
    else                     return $result[0]['post_time'];
}

/**
 * Returns an array of all the moderators of a forum
 */

function get_moderators($forum_id, $db)
{
	global $tbl_users, $tbl_forum_mods;

	$sql = "SELECT u.user_id, u.username
	        FROM `".$tbl_users."` u, `".$tbl_forum_mods."` f
	        WHERE f.forum_id = '".$forum_id."'
	        AND f.user_id = u.user_id";

	return claro_sql_query_fetch_all($sql);
}

/**
 * Checks if a user (user_id) is a moderator of a perticular forum (forum_id)
 * Retruns 1 if TRUE, 0 if FALSE or Error
 */
function is_moderator($forum_id, $user_id, $db)
{
    global $tbl_forum_mods;

    $sql = "SELECT user_id
            FROM `".$tbl_forum_mods."`
            WHERE forum_id = '".$forum_id."'
            AND user_id = '".$user_id."'";

    $result = claro_sql_query_fetch_all($sql);

    if (count($result) > 0 && $result[0]['user_id'] != '')
    {
        return 1;
    }
    else
    {
        return 0;
    }
}


/**
 * Returns a count of the given userid's private messages.
 * @author Nathan Codding - July 19, 2000
 */
function get_pmsg_count($user_id, $db)
{
	global $tbl_priv_msgs;

	$sql = "SELECT COUNT(msg_id)
            FROM `".$tbl_priv_msgs."`
	        WHERE to_userid = '".$user_id."'";

	return claro_sql_query_get_single_value($sql);
}


/**
 * Checks if a forum or a topic exists in the database. Used to prevent
 * users from simply editing the URL to post to a non-existant forum or topic
 */
function does_exists($id, $db, $type)
{
	global $tbl_forums, $tbl_topics;

	switch($type)
	{
		case 'forum':
			$sql = "SELECT COUNT(forum_id)
                    FROM `".$tbl_forums."` 
                    WHERE forum_id = '".$id."'";
		break;
		case 'topic':
			$sql = "SELECT COUNT(topic_id)
                    FROM `".$tbl_topics."`
                    WHERE topic_id = '".$id."'";
		break;
	}

    $itemCount = claro_sql_query_get_single_value($sql);

    if ($itemCount > 0) return 1;
    else                return 0;
}

/**
 * Checks if a topic is locked
 */
function is_locked($topic, $db)
{
	global $tbl_topics;

	$sql = "SELECT topic_status 
            FROM `".$tbl_topics."` 
            WHERE topic_id = '".$topic."'";

    $topicStatus = claro_sql_query_get_single_value($sql);

    if ($topicSatus == 1) return true;
    else                  return false;
}



/**
 * Escapes the "/" character with "\/". This is useful when you need
 * to stick a runtime string into a PREG regexp that is being delimited
 * with slashes.
 * @author Nathan Codding - Oct. 30, 2000
 */
function escape_slashes($input)
{
	$output = str_replace('/', '\/', $input);
	return $output;
}

/**
 * Returns the name of the forum based on ID number
 */
function get_forum_name($forum_id, $db)
{
	global $tbl_forums ;

	$sql = "SELECT forum_name
	        FROM `".$tbl_forums."`
	        WHERE forum_id = '".$forum_id."'";

	$forum_name = claro_sql_query_get_single_value($sql);
	if ($forum_name) return $forum_name;
	else             return 'None';
}


/**
 * Takes a string, and does the reverse of the PHP standard function
 * htmlspecialchars().
 * @author Nathan Codding - August 24, 2000.
 */
function undo_htmlspecialchars($input)
{
	$input = preg_replace('/&gt;/i'  , '>', $input);
	$input = preg_replace('/&lt;/i'  , '<', $input);
	$input = preg_replace('/&quot;/i', '"', $input);
	$input = preg_replace('/&amp;/i' , '&', $input);

	return $input;
}

/**
 * Check if this is the first post in a topic. Used in editpost.php
 */

function is_first_post($topic_id, $post_id)
{
	global $tbl_posts;

	$sql = "SELECT post_id FROM `".$tbl_posts."`
	        WHERE topic_id = '".$topic_id."'
	        ORDER BY post_id LIMIT 1";

	$id_found = claro_sql_query_get_single_value($sql);
    if ($id_found == $post_id) return 1;
    else                       return 0;
}

/**
 * Checks if the given userid is allowed to log into the given (private) forumid.
 * If the "is_posting" flag is true, checks if the user is allowed to post to that forum.
 */
function check_priv_forum_auth($userid, $forumid, $is_posting)
{
	global $tbl_forum_access;

	$sql = "SELECT count(*) AS user_count
	        FROM `".$tbl_forum_access."`
	        WHERE user_id = '".$userid."' 
              AND forum_id = '".$forumid."'";

	if ($is_posting) $sql .= "AND (can_post = 1)";

    $user_count  = claro_sql_query_get_single_value($sql);

	if ($user_count) return false;
    else             return true;
}

/**
 * Displays an error message and exits the script. Used in the posting files.
 */
function error_die($msg)
{
	global $tablewidth;
	global $db, $userdata, $user_logged_in, $starttime, $phpbbversion;

	echo "<table border=\"0\" align=\"center\" width=\"".$tablewidth."\">\n"
		."<tr>\n"
        ."<td>\n"
		."<blockquote>\n".$msg."\n</blockquote>\n"
		."</td>\n"
        ."</tr>\n"
	 	."</table>\n";

	 include('page_tail.php');
	 exit;
}

function make_jumpbox()
{
    global $l_selectforum, $l_go;
    global $tbl_categories, $tbl_forums;

    echo "<form action=\"viewforum.php\" method=\"get\">\n"
        ."<select name=\"forum\">\n"
        ."<option value=\"-1\">".$l_selectforum."</option>\n";

        $sql = "SELECT cat_id, cat_title
                FROM `".$tbl_categories."`
                ORDER BY cat_order";

        $catList = claro_sql_query_fetch_array($sql);

        foreach($catList as $thisCat)
        {
            echo "<option VALUE=\"-1\">&nbsp;</OPTION>\n"
                ."<option value=\"-1\">".$myrow['cat_title']."</option>\n"
                ."<option value=\"-1\">----------------</option>\n";

            $sql = "SELECT forum_id, forum_name 
                    FROM `".$tbl_forums."` 
                    WHERE cat_id = '".$myrow['cat_id']."' 
                    ORDER BY forum_id";

            $forumList = claro_sql_query_fetch_all($sql);

            if (count($forumList) > 0)
            {
                foreach($forumlist as $thisforum)
                {
                    echo "<option value=\"".$row['forum_id']."\">"
                        .stripslashes($row['forum_name'])
                        ."</option>\n";
            	}
            }
            else
            {
                echo "<option value=\"0\">no more forums</option>\n";
            }
        }

        echo "</select>\n"
            ."<input type=\"submit\" value=\"".$l_go."\">\n"
            ."</form>\n";
}

function get_translated_file($file)
{
	global $default_lang;

	// Try adding -default_lang to the filename. i.e.:
	// reply.jpg  becomes something like  reply-nederlands.jpg

	$trans_file = preg_replace("/(.*)(\..*?)/", "\\1-$default_lang\\2", $file);

	if(is_file($trans_file)) return $trans_file;
	else                     return $file;
}

function get_syslang_string($sys_lang, $string)
{
	$ret_string = $GLOBALS[$string];
	return($ret_string);
}


/**
 * Translates any sequence of whitespace (\t, \r, \n, or space) in the given
 * string into a single space character.
 * Returns the result.
 */
function normalize_whitespace($str)
{
	$output = "";

	$tok = preg_split('/[ \t\r\n]+/', $str);
	$tok_count = sizeof($tok);
	for ($i = 0; $i < ($tok_count - 1); $i++)
	{
		$output .= $tok[$i] . " ";
	}

	$output .= $tok[$tok_count - 1];

	return $output;
}

function sync($id, $type)
{
	global $tbl_posts, $tbl_topics, $tbl_forums, $tbl_forums;

	switch($type)
	{
		case 'forum':
			$sql = "SELECT MAX(post_id) AS last_post 
                    FROM `".$tbl_posts."` 
                    WHERE forum_id = '".$id."'";

			$last_post = claro_sql_query_get_single_value($sql);

			$sql = "SELECT COUNT(post_id) AS total 
                    FROM `".$tbl_posts."` 
                    WHERE forum_id = '".$id."'";

			$total_posts = claro_sql_query_get_single_value($sql);

			$sql = "SELECT COUNT(topic_id) AS total 
                    FROM `".$tbl_topics."` 
                    WHERE forum_id = '".$id."'";

			$total_topics = claro_sql_query_get_single_value($sql);

			$sql = "UPDATE `".$tbl_forums."`
			        SET forum_last_post_id = '".$last_post."',
			            forum_posts = '".$total_posts."',
			            forum_topics = '".$total_topics."'
			        WHERE forum_id = '".$id."'";

			$result = claro_sql_query($sql);
		break;

	case 'topic':
		$sql = "SELECT MAX(post_id) AS last_post 
                FROM `".$tbl_posts."` 
                WHERE topic_id = '".$id."'";

        $last_post = claro_sql_query_get_single_value($sql);

		$sql = "SELECT COUNT(post_id) AS total 
                FROM `".$tbl_posts."` 
                WHERE topic_id = '".$id."'";

	    $total_posts = claro_sql_query_get_single_value($sql);

		$sql = "UPDATE `".$tbl_topics."`
				SET topic_replies = '".$total_posts." #topic_replies should be renamed topic_posts', 
                topic_last_post_id = '".$last_post."'
				WHERE topic_id = '".$id."'";

		$result = claro_sql_query($sql);

	break;

	case 'all forums':
		$sql = "SELECT forum_id FROM `".$tbl_forums."`";
        $forumList = claro_sql_query_fetch_all($sql);

        foreach($forumList as $thisForum)
        {
        	$id = $thisForum['forum_id'];
            sync($db, $id, 'forum');
        }
        
	break;

	case 'all topics':
		$sql = "SELECT topic_id FROM `".$tbl_topics."`";
        $topicList = claro_sql_query_fetch_all($sql);

        foreach($topicList as $thisTopic)
        {
        	$id = $thisTopic['topic_id'];
  			sync($db, $id, "topic");
        }
        
	break;

	}				// end switch

	return true;
}


/**
 * Less agressive version of stripslashes. Only replaces \\ \' and \"
 * The PHP stripslashes() also removed single backslashes from the string.
 * Expects a string or array as an argument.
 * Returns the result.
 */
function own_stripslashes($string)
{
	$find = array(
			'/\\\\\'/',  // \\\'
			'/\\\\/',    // \\
				'/\\\'/',    // \'
			'/\\\"/');   // \"
	$replace = array(
			'\'',   // \
			'\\',   // \
			'\'',   // '
			'"');   // "
	return preg_replace($find, $replace, $string);
}

/**
 * Convert a SQL date or datetime to a unix time stamp
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param string SQL DATETIME or DATE
 * @return int unix time stamp
 */

function datetime_to_timestamp($dateTime)
{
    $dateTimeList = explode(' ', $dateTime);
    if ( count($dateTimeList) == 1 ) $dateTimeList[1] = '00:00:00'; // complete the missing time
    list($date, $time) = $dateTimeList;

    list($year, $month, $day) = explode('-', $date);
    list($hour, $min, $sec)   = explode(':', $time);

    return mktime($hour, $min, $sec, $month, $day, $year);
}

/**
 * Get the forum settings of a forum
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  int $forumId
 * @param  int $topicId (optional)
 * @return array forum settings
 */

function get_forum_settings($forumId, $topicId = -1)
{
    global $tbl_forums, $tbl_topics, $tbl_student_group;

    $sql = "SELECT `f`.`forum_id`     `forum_id`,
                   `f`.`forum_name`   `forum_name`,
                   `f`.`forum_access` `forum_access`,
                   `f`.`forum_type`   `forum_type`,
                   `g`.`id`           `idGroup`

            FROM `".$tbl_forums."` `f`";
            
    $sql .= ($topicId != -1) ? ", `".$tbl_topics."` `t` \n" : "\n";

    $sql .= "# Check possible attached group ...
             LEFT JOIN `".$tbl_student_group."` `g`
                    ON `f`.`forum_id` = `g`.`forumId`

             WHERE `f`.`forum_id` = '".$forumId."'";

    if ($topicId != -1)
    {
    	$sql .= "\nAND `t`.`topic_id` = '".$topicId."'
                   AND `t`.`forum_id` = f.forum_id";
    }

    $result = claro_sql_query_fetch_all($sql);

    if ( count($result) == 1) return $result[0];
    else                      error_die('Unexisting forum.');
}

/**
 * Get topic settings of a topic
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  int $topicId
 * @return array topic settings
 */

function get_topic_settings($topicId)
{
    global $tbl_topics;

    $sql = "SELECT topic_id, topic_title, topic_status, forum_id , 
                   topic_poster, topic_time, topic_views, 
                   topic_replies, topic_last_post_id, topic_notify, 
                   nom, prenom
            FROM `".$tbl_topics."` 
            WHERE topic_id = '".$topicId."'";

    $settingList = claro_sql_query_fetch_all($sql);

    if ( count($settingList) == 1) $settingList = $settingList[0];
    else                           error_die('Unexisting topic.');

    return $settingList;
}

/**
 * create a new topic
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param string $subject
 * @param string $time
 * @param int $forumId
 * @param int $userId
 * @param string $userFirstname
 * @param string $userLastname
 * @return 
 */

function create_new_topic($subject, $time, $forumId, 
                          $userId, $userFirstname, $userLastname)
{
    
    global $tbl_topics, $tbl_forums;

    $sql = "INSERT INTO `".$tbl_topics."` 
            SET topic_title  = '".$subject."', 
                topic_poster = '".$userId."', 
                forum_id     = '".$forumId."', 
                topic_time   = '".$time."', 
                topic_notify = 1,
                nom          = '".$userLastname."', 
                prenom       = '".$userFirstname."'";

    $topicId = claro_sql_query_insert_id($sql);

    // UPDATE THE TOPIC STATUS FOR THE CURRENT FORUM

    $sql = "UPDATE `".$tbl_forums."` 
            SET   forum_topics = forum_topics+1
            WHERE forum_id     = '".$forumId."'";

    $result = claro_sql_query($sql);

    return $topicId;
}

/**
 * 
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param
 * @return 
 */


function create_new_post($topicId, $forumId, $userId, $time, $posterIp, 
                         $userLastname, $userFirstname, $message)
{
    global $tbl_posts, $tbl_posts_text, $tbl_topics, 
           $tbl_users, $tbl_forums;

    // CREATE THE POST SETTINGS

    $sql = "INSERT INTO `".$tbl_posts."`
            SET topic_id  = '".$topicId."', 
                forum_id  = '".$forumId."', 
                poster_id = '".$userId."', 
                post_time = '".$time."', 
                poster_ip = '".$posterIp."', 
                nom       = '".$userLastname."', 
                prenom    = '".$userFirstname."'";

    $postId = claro_sql_query_insert_id($sql);

    if ($postId)
    {
        // RECORD THE POST CONTENT

        $sql = "INSERT INTO `".$tbl_posts_text."` 
                SET post_id   = '".$postId."', 
                    post_text = '".$message."'";

        $result = claro_sql_query($sql);

        // UPDATE THE TOPIC STATUS

        $sql = "UPDATE `".$tbl_topics."` 
                SET   topic_replies      =  topic_replies+1, # should be transformed into `topic_posts`
                      topic_last_post_id = '".$postId."',
                      topic_time         = '".$time."' 
                WHERE topic_id = '".$topicId."'";

        $result = claro_sql_query($sql);

//        // UPDATE THE POST NUMBER STATUS FOR THE CURRENT USER
//
//        if($userId != -1)
//        {
//            $sql = "UPDATE `".$tbl_users."` 
//                    SET   user_posts = user_posts+1 
//                    WHERE user_id = '".$userId."'";
//
//            $result = claro_sql_query($sql);
//        }

        // UPDATE THE POST STATUS FOR THE CURRENT FORUM

        $sql = "UPDATE `".$tbl_forums."` 
                SET   forum_posts        = forum_posts+1, 
                      forum_last_post_id = '".$postId."' 
                WHERE forum_id           = '".$forumId."'";

        $result = claro_sql_query($sql);

        return $postId;
    }
    else
    {
    	return false;
    }
}


/**
 * 
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param
 * @return 
 */


function update_post($postId, $message, $subject = '')
{
    global $tbl_posts_text, $tbl_topics;

    $sql = "UPDATE `".$tbl_posts_text."` 
            SET post_text = '".$message."' 
            WHERE post_id = '".$postId."'";

    $result = claro_sql_query($sql);

    if ($subject != '')
    {
        $sql = "UPDATE `".$tbl_topics."` 
                SET topic_title  = '".$subject."', 
                    topic_notify = '".$notify."' 
                WHERE topic_id = '".$postId."'";

        $result = claro_sql_query($sql);
    }
}

/**
 * 
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param
 * @return 
 */

function delete_post($postId, $topicId, $forumId, $userId)
{
    global $tbl_posts, $tbl_posts_text, 
           $tbl_topics, $tbl_users;

    $sql = "DELETE FROM `".$tbl_posts."` 
            WHERE post_id = '".$postId."'";

    $result = claro_sql_query($sql);

    $sql = "DELETE FROM `".$tbl_posts_text."` 
            WHERE post_id = '".$postId."'";

    $result = claro_sql_query($sql);


    if( get_total_posts($topicId, 'topic') == 0 ) # warning $db poses 
                                                       # problems, we have to 
                                                       # remove it.
    {
        $sql = "DELETE FROM `".$tbl_topics."` 
                WHERE topic_id = '".$topicId."'";

        $result = claro_sql_query($sql);
        $topic_removed = true;
    }
    else
    {
        $sql = "UPDATE `".$tbl_topics."` 
                SET topic_time = '". get_last_post($topicId, 'topic')."' 
                WHERE topic_id = '".$topicId."'";

        $result = claro_sql_query($sql);
    }

//    if($userId != -1)
//    {
//        $sql = "UPDATE `".$tbl_users."` 
//                SET user_posts = user_posts - 1 
//                WHERE user_id = '".$userId."'";
//
//        $result = claro_sql_query($sql);
//    }

    // don't understand these two lines below    
    sync($forumId, 'forum');
    if(!$topic_removed) sync($topicId, 'topic');

}


/**
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param int $userId
 * @param int $topicId
 * @return void
 */

function request_topic_notification($userId, $topicId)
{
    global $tbl_user_notify;

    // check first if user is not regisitered for topic notification yet
    if (! is_topic_notification_requested($userId, $topicId) )
    {   
        $sql = "INSERT INTO `".$tbl_user_notify."`
                SET `user_id`  = '".$userId."',
                    `topic_id` = '".$topicId."'";

        claro_sql_query($sql);
    }
}

/**
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param int $userId
 * @param int $topicId
 * @return void
 */

function cancel_topic_notification($userId, $topicId)
{
    global $tbl_user_notify;

    $sql = "DELETE FROM `".$tbl_user_notify."`
            WHERE `user_id`  = '".$userId."'
              AND `topic_id` = '".$topicId."'";

    claro_sql_query($sql);
}

/**
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param int $userId
 * @param int $topicId
 * @return bool
 */

function is_topic_notification_requested($userId, $topicId)
{
    global $tbl_user_notify;

    $sql = "SELECT COUNT(*) 
            FROM `".$tbl_user_notify."`
            WHERE `user_id`  = '".$userId."'
              AND `topic_id` = '".$topicId."'";

    if (claro_sql_query_get_single_value($sql) > 0) return true;
    else                                            return false;
}


function trig_topic_notification($topicId)
{
    global $tbl_user_notify, $tbl_users, $sys_lang;
    global $langDear, $l_notifybody, $l_notifysubj;
    global $url_phpbb, $_course;

    $sql = "SELECT u.user_id, u.prenom firstname, u.nom lastname
            FROM `".$tbl_user_notify."` AS notif, 
                 `".$tbl_users."` AS u
            WHERE notif.topic_id = '".$topicId."'
            AND   notif.user_id  = u.user_id";

    $notifyResult = claro_sql_query($sql);
    $subject      = $l_notifysubj;

    $url_topic = "http://" . $_SERVER['SERVER_NAME'] . $url_phpbb . "/viewtopic.php?topic=". $topicId . "&amp;cidReq=" . $_course['sysCode'];
    $url_forum = "http://" . $_SERVER['SERVER_NAME'] . $url_phpbb . "/index.php?cidReq=" . $_course['sysCode'];

    // send mail to registered user for notification

    while ($list = mysql_fetch_array($notifyResult))
    {
       $message = $langDear . " " . $list['firstname']." ".$list['lastname'].",\n\n";
       $message.= sprintf($l_notifybody,$url_topic,$url_forum);

       claro_mail_user($list['user_id'], $message, $subject);
    }
}


/**
 * Display formated message with several 'return to ...' possibility
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param string $message
 * @param int $forumId (optional)
 * @param int $topicId (optional)
 * @return void
 */

function disp_confirmation_message ($message, $forumId = false, $topicId = false)
{
    global $tablewidth;
    global $l_click, $l_here, $l_viewmsg, $l_returntopic, $l_returnindex;

    echo "<table border=\"0\" align=\"center\" width=\"".$tablewidth."\">"
        
        ."<tr>\n"
        ."<td>\n"
        ."<center>\n"
        ."<p>".$message."</p>\n";

        if ($forumId && $topicId)
        {
            echo "<p>"
                .$l_click
                ." <a href=\"viewtopic.php?topic=".$topicId."&amp;forum=".$forumId."\">"
                .$l_here
                ."</a> "
                .$l_viewmsg
                ."</p>\n";
        }
        
        if ($forumId)
        {
            echo "<p>"
                .$l_click
                ." <a href=\"viewforum.php?forum=".$forumId."\">"
                .$l_here
                ."</a> " 
                .$l_returntopic
                ."</p>\n";
        }

        echo "<p>"
            .$l_click
            ." <a href=\"index.php\">"
            .$l_here
            ."</a> "
            .$l_returnindex
            ."</p>"
        
            ."</center>\n"
            ."</td>\n"
            ."</tr>\n"
        
            ."</table>\n";
}

/**
 * Display a mini pager. At the opposite of the claro_sql_pager, it doesn't 
 * depend of SQL, but you have to know before the total count of item.
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param string $url - url to be used
 * @param string $offsetParam - param to introduce to call the pager offset
 * @param int    $total - total number of items
 * @param int    $step  - step between each offset
 * @parm  int    $pageMax (optionnal) - If the number of page exceeds this param
 *               the remaining pages are replaced by a '...' except the last one.
 * @return void 
 */


function disp_mini_pager($url, $offsetParam, $total, $step, $pageMax = 3)
{
    $pageList  = array();
    $pageNum   = 1;
    $skip      = false;

    if ( $total < $step      ) return; // no need to go further
    if ( ! strpos($url, '?') ) $glue = '?';
    else                       $glue = '&amp;';

    for($offset = 0; $offset < $total; $offset += $step)
    {
        $isLastPage = (bool) ( ($x + $step) >= $total);

        if ($pageNum < $pageMax || $isLastPage)
        {
        	$pageList[] = '<a href="'.$url.$glue.$offsetParam.'='.$offset.'">'
                         .$pageNum
                         .'</a>';
        }
        elseif (! $skip)
        {
        	$pageList[] = '...'; // actually first time one have to skip
            $skip       = true;
        }
           
        $pageNum++;
    }

    if (count($pageList) > 0)
    {
        echo "<small>(".implode(', ', $pageList).")</small>";	
    }
}

/**
 * Class building a list of all the post of specific topic, with pager options
 * The class is actually based on the claro_sql_pager class
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @see    claro_sql_pager class
 */

class postLister
{
    var $sqlPager;

    /**
     * class constructor
     *
     * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
     * @param int $topicId     id of the current topic
     * @param int $start       post where to start
     * @param int $postPerPage number of post to display per page
     */

    function postLister($topicId, $start = 1, $postsPerPage)
    {
        global $tbl_posts, $tbl_posts_text, $includePath;

        $sql = "SELECT  p.`post_id`,   p.`topic_id`,  p.`forum_id`,
                        p.`poster_id`, p.`post_time`, p.`poster_ip`,
                        p.`nom` lastname, p.`prenom` firstname,
                        pt.`post_text` 

               FROM     `".$tbl_posts."`      p, 
                        `".$tbl_posts_text."` pt 

               WHERE    topic_id  = '".$topicId."' 
                 AND    p.post_id = pt.`post_id`

               ORDER BY post_id";

        require_once $includePath.'/lib/pager.lib.php';

        $this->sqlPager = new claro_sql_pager($sql, $start, $postsPerPage);

        $this->sqlPager->set_pager_call_param_name('start');
    }

    /**
     * return all the post list of the current topic
     *
     * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
     * @return array post list
     */

    function get_post_list()
    {
        return $this->sqlPager->get_result_list();
    }

    /**
     * display a pager tool bar
     *
     * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
     * @param string $url page where to point
     * @return void
     */

    function disp_pager_tool_bar($pagerUrl)
    {
        $this->sqlPager->disp_pager_tool_bar($pagerUrl);
    }
}

?>
