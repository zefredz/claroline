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

/**
 * Start session-management functions - Nathan Codding, July 21, 2000.
 */

/**
 * new_session()
 * Adds a new session to the database for the given userid.
 * Returns the new session ID.
 * Also deletes all expired sessions from the database, based on the given session lifespan.
 */
function new_session($userid, $remote_ip, $lifespan, $db)
{
	global $tbl_sessions;

	mt_srand((double)microtime()*1000000);
	$sessid = mt_rand();

	$currtime = (string) (time());
	$expirytime = (string) (time() - $lifespan);

	$deleteSQL = "DELETE FROM `$tbl_sessions` WHERE (start_time < $expirytime)";
	$delresult = mysql_query($deleteSQL, $db);

	if (!$delresult) die("Delete failed in new_session()");

	$result = mysql_query("INSERT INTO `$tbl_sessions`
	                      (sess_id, user_id, start_time, remote_ip)
	                       VALUES
	                      ($sessid, $userid, $currtime, '$remote_ip')", $db)
	          or die(mysql_errno().": ".mysql_error()."<br>Insert failed in new_session()");

	return $sessid;
}												// end new_session()

/**
 * Sets the sessID cookie for the given session ID. the $cookietime parameter
 * is no longer used, but just hasn't been removed yet. It'll break all the modules
 * (just login) that call this code when it gets removed.
 * Sets a cookie with no specified expiry time. This makes the cookie last until the
 * user's browser is closed. (at last that's the case in IE5 and NS4.7.. Haven't tried
 * it with anything else.)
 */
function set_session_cookie($sessid, $cookietime, $cookiename, $cookiepath, $cookiedomain, $cookiesecure)
{
	// This sets a cookie that will persist until the user closes their browser window.
	// since session expiry is handled on the server-side, cookie expiry time isn't a big deal.
	setcookie($cookiename,$sessid,'',$cookiepath,$cookiedomain,$cookiesecure);

}				// set_session_cookie()


/**
 * Returns the userID associated with the given session, based on
 * the given session lifespan $cookietime and the given remote IP
 * address. If no match found, returns 0.
 */
function get_userid_from_session($sessid, $cookietime, $remote_ip, $db)
{
	global $tbl_sessions;

	$mintime = time() - $cookietime;

	$result = mysql_query("SELECT user_id FROM `$tbl_sessions`
	                       WHERE (sess_id = $sessid)
	                       AND (start_time > $mintime)
	                       AND (remote_ip = '$remote_ip')", $db)
	          or die("Error doing DB query in get_userid_from_session()");

	$row = mysql_fetch_array($result);

	if (!$row)	return 0;
	else		return $row[user_id];

}				// get_userid_from_session()

/**
 * Refresh the start_time of the given session in the database.
 * This is called whenever a page is hit by a user with a valid session.
 */
function update_session_time($sessid, $db)
{
	global $tbl_sessions;

	$newtime = (string) time();

	$result = mysql_query("UPDATE `$tbl_sessions`
	                       SET start_time=$newtime
	                       WHERE (sess_id = $sessid)", $db)
	          or die(mysql_error()."<br>Error doing DB update in update_session_time()");
	return 1;
}												// update_session_time()

/**
 * Delete the given session from the database. Used by the logout page.
 */
function end_user_session($userid, $db)
{
	global $tbl_sessions;

	$result = mysql_query("DELETE FROM `$tbl_sessions`
	                       WHERE (user_id = $userid)", $db)
	          or die (mysql_error() . "<br>Delete failed in end_user_session()");
	return 1;
}				// end_session()

/**
 * Prints either "logged in as [username]. Log out." or
 * "Not logged in. Log in.", depending on the value of
 * $user_logged_in.
 */
function print_login_status($user_logged_in, $username, $url_phpbb)
{
	global $phpEx;
	global $l_loggedinas, $l_notloggedin, $l_logout, $l_login;

	if($user_logged_in)
	{
		echo	"<b>",$l_loggedinas," ",$username,". ",
				"<a href=\"",$url_phpbb,"/logout.",$phpEx,"\">",$l_logout,".</a>",
				"</b><br>\n";
	}
	else
	{
		echo	"<b>",$l_notloggedin,". ",
				"<a href=\"",$url_phpbb,"/login.",$phpEx,"\">",$l_login,".</a>",
				"</b><br>\n";
	}
}				// print_login_status()

/**
 * Prints a link to either login.php or logout.php, depending
 * on whether the user's logged in or not.
 */

function make_login_logout_link($user_logged_in, $url_phpbb)
{
	global $phpEx;

	global $l_logout, $l_login;

	if ($user_logged_in)
	{
		$link = "<a href=\"$url_phpbb/logout.$phpEx\">$l_logout</a>";
	}
	else
	{
		$link = "<a href=\"$url_phpbb/login.$phpEx\">$l_login</a>";
	}

	return $link;
}				// make_login_logout_link()


/*---------------------- End session-management functions -------------------*/

/**
 * Gets the total number of topics in a form
 */
function get_total_topics($forum_id, $db)
{
	global $tbl_topics;

	$sql = "SELECT COUNT(*) AS total
	        FROM `".$tbl_topics."`
	        WHERE forum_id = '".$forum_id."'";

    return claro_sql_query_get_single_value($sql);
}

/**
 * Shows the 'header' data from the header/meta/footer table
 */
function showheader($db) { /* ... */ }

/**
 * Shows the meta information from the header/meta/footer table
 */
function showmeta($db)  { /* ... */ }

/**
 * Show the footer from the header/meta/footer table
 */
function showfooter($db)  { /* ... */ }

/**
 * Used to keep track of all the people viewing the forum at this time
 * Anyone who's been on the board within the last 300 seconds will be
 * returned. Any data older then 300 seconds will be removed
 */
function get_whosonline($IP, $username, $forum, $db)
{
	global $sys_lang, $tbl_whosonline;

	if($username == '') $username = get_syslang_string($sys_lang, "l_guest");

	$time       = explode(  " ", microtime());
	$userusec   = (double)$time[0];
	$usersec    = (double)$time[1];
	$username   = addslashes($username);

	$deleteuser = mysql_query( "DELETE FROM `$tbl_whosonline` WHERE date < $usersec - 300", $db);
	$userlog    = mysql_fetch_row(MYSQL_QUERY( "SELECT * FROM `$tbl_whosonline` where IP = '$IP'", $db));
	if($userlog == false)
	{
		$ok= @mysql_query( "INSERT INTO `$tbl_whosonline`
		                    (ID,IP,DATE,username,forum)
		                    VALUES
		                    ('$User_Id','$IP','$usersec', '$username', '$forum')", $db)
		     or die( "Unable to query db!");
	}

	$resultlogtab   = mysql_query("SELECT Count(*) as total FROM `$tbl_whosonline`", $db);
	$numberlogtab   = mysql_fetch_array($resultlogtab);
	return($numberlogtab[total]);
}

/**
 * Returns the total number of posts in the whole system, a forum, or a topic
 * Also can return the number of users on the system.
 */
function get_total_posts($id, $db, $type)
{
	global $tbl_users, $tbl_posts;

	switch($type)
	{
		case 'users':
			$sql = "SELECT COUNT(*) AS total FROM `".$tbl_users."` WHERE (user_id != -1) AND (user_level != -1)";
		break;
		case 'all':
			$sql = "SELECT COUNT(*) AS total FROM `".$tbl_posts."`";
		break;
		case 'forum':
			$sql = "SELECT COUNT(*) AS total FROM `".$tbl_posts."` WHERE forum_id = '".$id."'";
		break;
		case 'topic':
			$sql = "SELECT COUNT(*) AS total FROM `".$tbl_posts."` WHERE topic_id = '".$id."'";
		break;
		// Old, we should never get this.
		case 'user':
			die("Should be using the users.user_posts column for this.");
	}

	return claro_sql_query_get_single_value($sql);
}

/**
 * Returns the most recent post in a forum, or a topic
 */
function get_last_post($id, $db, $type)
{
	global $l_error, $l_noposts, $l_by, $tbl_posts, $tbl_users ;

	switch($type)
	{
		case 'time_fix':
			$sql = "SELECT p.post_time 
                    FROM `".$tbl_posts."` p
			        WHERE p.topic_id = '".$id."'
			        ORDER BY post_time DESC LIMIT 1";
		break;

		case 'forum':
			$sql = "SELECT p.post_time, p.poster_id, u.username
			        FROM `".$tbl_posts."` p, `".$tbl_users."` u
		            WHERE p.forum_id = '".$id."'
		              AND p.poster_id = u.user_id
		            ORDER BY post_time DESC LIMIT 1";
		break;

		case 'topic':
			$sql = "SELECT p.post_time, u.username
			        FROM `".$tbl_posts."` p, `$tbl_users` u
			        WHERE p.topic_id = '".$id."'
			          AND p.poster_id = u.user_id
			        ORDER BY post_time DESC LIMIT 1";
		break;

		case 'user':
			$sql = "SELECT p.post_time
			        FROM `".$tbl_posts."` p
			        WHERE p.poster_id = '".$id."'
			        LIMIT 1";
		break;
	}

	if(!$result = mysql_query($sql, $db))       return($l_error);
	if(!$myrow = mysql_fetch_array($result))    return($l_noposts);

	if(($type != 'user') && ($type != 'time_fix'))
	{
		$val = sprintf("%s <br> %s %s", $myrow['post_time'], $l_by, $myrow['username']);
	}
	else
	{
		$val = $myrow['post_time'];
	}

	return($val);
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

	if(!$result = mysql_query($sql, $db))    return(array());
	if(!$myrow = mysql_fetch_array($result)) return(array());

	do
	{
		$array[] = array("$myrow[user_id]" => "$myrow[username]");

	}	while($myrow = mysql_fetch_array($result));

	return($array);
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
	        AND user_id = '$user_id'";

	if(!$result = mysql_query($sql, $db))    return("0");
	if(!$myrow = mysql_fetch_array($result)) return("0");
	if($myrow[user_id] != '')                return("1");
	else                                     return("0");
}

/**
 * Checks the given password against the DB for the given username.
 * Returns true if good, false if not.
 * @author  Nathan Codding - July 19, 2000
 */
function check_user_pw($username, $password, $db) { return 0;}


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
 * Checks if a given username exists in the DB. Returns true if so, false if not.
 * @author Nathan Codding - July 19, 2000
 */
function check_username($username, $db)
{
	$username = addslashes($username);

	$sql = "SELECT user_id FROM `$tbl_users`
	        WHERE (username = '$username')
	        AND (user_level != '-1')";

	$resultID = mysql_query($sql)
	            or die(mysql_error() . "<br>Error doing DB query in check_username()");

	return mysql_num_rows($resultID);
}				// check_username()


/**
 * Nathan Codding, July 19/2000
 * Get a user's data, given their user ID.
 */

function get_userdata_from_id($userid, $db)
{
	global $tbl_users;

	$sql = "SELECT * FROM `$tbl_users`
	        WHERE user_id = $userid";

	if(!$result = mysql_query($sql, $db))
	{
		$userdata = array("error" => "1");
		return ($userdata);
	}

	if(!$myrow = mysql_fetch_array($result))
	{
		$userdata = array("error" => "1");
		return ($userdata);
	}

	return($myrow);
}

/**
 * Gets user's data based on their username
 */
function get_userdata($username, $db)
{
	global $tbl_users;

	$username = addslashes($username);

	$sql = "SELECT * FROM `$tbl_users`
	        WHERE username = '$username'
	        AND user_level != -1";

	if(!$result = mysql_query($sql, $db))    $userdata = array("error" => "1");
	if(!$myrow = mysql_fetch_array($result)) $userdata = array("error" => "1");

	return($myrow);
}

/**
 * Returns all the rows in the themes table
 */
function setuptheme($theme, $db) { /* ... */ }


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
                    FROM `$tbl_forums` 
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
 * Changes :) to an <IMG> tag based on the smiles table in the database.
 *
 * Smilies must be either:
 * 	- at the start of the message.
 * 	- at the start of a line.
 * 	- preceded by a space or a period.
 * This keeps them from breaking HTML code and BBCode.
 * TODO: Get rid of global variables.
 */
function smile($message)
{
	global $db, $url_smiles, $tbl_smiles;

	// Pad it with a space so the regexp can match.
	$message = ' ' . $message;

	if ($getsmiles = mysql_query("SELECT *, length(code) as length
	                              FROM `$tbl_smiles`
	                              ORDER BY length DESC"))
	{
		while ($smiles = mysql_fetch_array($getsmiles))
		{
			$smile_code = preg_quote($smiles[code]);
			$smile_code = str_replace('/', '//', $smile_code);
			$message = preg_replace("/([\n\\ \\.])$smile_code/si",
			                         '\1<IMG SRC="' . $url_smiles . '/' . $smiles[smile_url] . '">',
			                          $message);
		}
	}

	// Remove padding, return the new string.
	$message = substr($message, 1);
	return($message);
}

/**
 * Changes a Smiliy <IMG> tag into its corresponding smile
 * TODO: Get rid of golbal variables, and implement a method of
 * distinguishing between :D and :grin: using the <IMG> tag
 */
function desmile($message)
{
	// Ick Ick Global variables...remind me to fix these! - theFinn
	global $db, $url_smiles, $tbl_smiles;

	if ($getsmiles = mysql_query("SELECT * FROM `".$tbl_smiles."`"))
	{
		while ($smiles = mysql_fetch_array($getsmiles))
		{
			$message = str_replace("<IMG SRC=\"$url_smiles/$smiles[smile_url]\">",
			                        $smiles[code], $message);
		}
	}

	return($message);
}

/**
 * bbdecode/bbencode functions:
 * Rewritten - Nathan Codding - Aug 24, 2000
 * quote, code, and list rewritten again in Jan. 2001.
 * All BBCode tags now implemented. Nesting and multiple occurances should be
 * handled fine for all of them. Using str_replace() instead of regexps often
 * for efficiency. quote, list, and code are not regular, so they are
 * implemented as PDAs - probably not all that efficient, but that's the way it is.
 *
 * Note: all BBCode tags are case-insensitive.
 */

function bbencode($message, $is_html_disabled) { return $message; }
function bbdecode($message) {return($message);}

/**
 * This function does exactly what the PHP4 function array_push() does
 * however, to keep phpBB compatable with PHP 3 we had to come up with out own
 * method of doing it.
 * @author James Atkinson - Feb 5, 2001
 */
function bbcode_array_push(&$stack, $value)
{
   $stack[] = $value;
   return(sizeof($stack));
}

/**
 * This function does exactly what the PHP4 function array_pop() does
 * however, to keep phpBB compatable with PHP 3 we had to come up with out own
 * method of doing it.
 * @author James Atkinson - Feb 5, 2001
 */

function bbcode_array_pop(&$stack)
{
	$arrSize = count($stack);
	$x = 1;

	while(list($key, $val) = each($stack))
	{
		if($x < count($stack))
		{
			$tmpArr[] = $val;
		}
		else
		{
			$return_val = $val;
		}
		$x++;
	}

	$stack = $tmpArr;
	return($return_val);
}

/**
 * Performs [quote][/quote] bbencoding on the given string, and returns the results.
 */
function bbencode_quote($message) { return $message; }


/**
 * Performs [code][/code] bbencoding on the given string, and returns the results.
 */

function bbencode_code($message, $is_html_disabled) { return $message; }



/**
 * Performs [list][/list] and [list=?][/list] bbencoding on the given string, and returns the results.
 */
function bbencode_list($message) { return $message; }



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

/*
 * Returns the name of the forum based on ID number
 */
function get_forum_name($forum_id, $db)
{
	global $tbl_forums ;

	$sql = "SELECT forum_name
	        FROM `".$tbl_forums."`
	        WHERE forum_id = '".$forum_id."'";

	if(!$r = mysql_query($sql, $db)) return("ERROR");
	if(!$m = mysql_fetch_array($r))  return("None");

	return($m[forum_name]);
}



/**
 * Rewritten by Nathan Codding - Feb 6, 2001.
 * - Goes through the given string, and replaces xxxx://yyyy with an HTML <a> tag linking
 * 	to that URL
 * - Goes through the given string, and replaces www.xxxx.yyyy[zzzz] with an HTML <a> tag linking
 * 	to http://www.xxxx.yyyy[/zzzz]
 * - Goes through the given string, and replaces xxxx@yyyy with an HTML mailto: tag linking
 *		to that email address
 * - Only matches these 2 patterns either after a space, or at the beginning of a line
 *
 * Notes: the email one might get annoying - it's easy to make it more restrictive, though.. maybe
 * have it require something like xxxx@yyyy.zzzz or such. We'll see.
 */

function make_clickable($text)
{

	// pad it with a space so we can match things at the start of the 1st line.
	$ret = " " . $text;

	// matches an "xxxx://yyyy" URL at the start of a line, or after a space.
	// xxxx can only be alpha characters.
	// yyyy is anything up to the first space, newline, or comma.
	$ret = preg_replace("#([\n ])([a-z]+?)://([^, \n\r]+)#i", "\\1<!-- BBCode auto-link start --><a href=\"\\2://\\3\" target=\"_blank\">\\2://\\3</a><!-- BBCode auto-link end -->", $ret);

	// matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
	// Must contain at least 2 dots. xxxx contains either alphanum, or "-"
	// yyyy contains either alphanum, "-", or "."
	// zzzz is optional.. will contain everything up to the first space, newline, or comma.
	// This is slightly restrictive - it's not going to match stuff like "forums.foo.com"
	// This is to keep it from getting annoying and matching stuff that's not meant to be a link.
	$ret = preg_replace("#([\n ])www\.([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[^, \n\r]*)?)#i", "\\1<!-- BBCode auto-link start --><a href=\"http://www.\\2.\\3\\4\" target=\"_blank\">www.\\2.\\3\\4</a><!-- BBCode auto-link end -->", $ret);

	// matches an email@domain type address at the start of a line, or after a space.
	// Note: before the @ sign, the only valid characters are the alphanums and "-", "_", or ".".
	// After the @ sign, we accept anything up to the first space, linebreak, or comma.
	$ret = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([^, \n\r]+)#i", "\\1<!-- BBcode auto-mailto start --><a href=\"mailto:\\2@\\3\">\\2@\\3</a><!-- BBCode auto-mailto end -->", $ret);

	// Remove our padding..
	$ret = substr($ret, 1);

	return($ret);
}


/**
 * Reverses the effects of make_clickable(), for use in editpost.
 * - Does not distinguish between "www.xxxx.yyyy" and "http://aaaa.bbbb" type URLs.
 * @author Nathan Codding - Feb 6, 2001
 */

function undo_make_clickable($text)
{
	$text = preg_replace("#<!-- BBCode auto-link start --><a href=\"(.*?)\" target=\"_blank\">.*?</a><!-- BBCode auto-link end -->#i", "\\1", $text);
	$text = preg_replace("#<!-- BBcode auto-mailto start --><a href=\"mailto:(.*?)\">.*?</a><!-- BBCode auto-mailto end -->#i", "\\1", $text);

	return $text;
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
 * Make sure a username isn't on the disallow list
 */

function validate_username($username, $db)
{
	global $tbl_disallow;

	$sql = "SELECT disallow_username
	        FROM `".$tbl_disallow."`
	        WHERE disallow_username = '" . addslashes($username) . "'";

	if(!$r = mysql_query($sql, $db)) return(0);

	if($m = mysql_fetch_array($r))
	{
		if($m['disallow_username'] == $username) return(1);
		else                                     return(0);
	}

	return(0);
}

/**
 * Check if this is the first post in a topic. Used in editpost.php
 */

function is_first_post($topic_id, $post_id, $db)
{
	global $tbl_posts;

	$sql = "SELECT post_id FROM `".$tbl_posts."`
	        WHERE topic_id = '".$topic_id."'
	        ORDER BY post_id LIMIT 1";

	if(!$r = mysql_query($sql, $db)) return(0);
	if(!$m = mysql_fetch_array($r))  return(0);
	if($m[post_id] == $post_id)      return(1);
	else                             return(0);
}

/**
 * Replaces banned words in a string with their replacements
 */

function censor_string($string, $db)
{
	global $tbl_words;

	$r = mysql_query("SELECT word, replacement FROM `$tbl_words`", $db)
	     or error_die("Error, could not contact the database!
	             Please check your database settings in config.php");

	while($w = mysql_fetch_array($r))
	{
		$word        = quotemeta(stripslashes($w[word]));
		$replacement = stripslashes($w[replacement]);
		$string      = eregi_replace(" $word", " $replacement", $string);
		$string      = eregi_replace("^$word", "$replacement", $string);
		$string      = eregi_replace("<BR>$word", "<BR>$replacement", $string);
	}

	return($string);
}

function is_banned($ipuser, $type, $db)
{
	global $tbl_banlist;

	// Remove old bans
	$sql = "DELETE FROM `$tbl_banlist`
	        WHERE (ban_end < ". mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")).")
	        AND (ban_end > 0)";

	@mysql_query($sql, $db);

	switch($type)
	{
		case "ip":
			$sql = "SELECT ban_ip FROM `$tbl_banlist`";

			if($r = mysql_query($sql, $db))
			{
				while($iprow = mysql_fetch_array($r))
				{
					$ip = $iprow[ban_ip];
					if($ip[strlen($ip) - 1] == ".")
					{
						$db_ip = explode(".", $ip);
						$this_ip = explode(".", $ipuser);

						for($x = 0; $x < count($db_ip) - 1; $x++) $my_ip .= $this_ip[$x] . ".";

						if($my_ip == $ip) return(TRUE);
					}
					else
					{
						if($ipuser == $ip)
						return(TRUE);
					}
				}
			}
			else return(FALSE);
		break;

		case "username":
			$sql = "SELECT ban_userid FROM `$tbl_banlist`
			        WHERE ban_userid = '$ipuser'";
			if($r = mysql_query($sql, $db))
			{
				if(mysql_num_rows($r) > 0) return(TRUE);
			}
		break;
	}

	return(FALSE);
}

/**
 * Checks if the given userid is allowed to log into the given (private) forumid.
 * If the "is_posting" flag is true, checks if the user is allowed to post to that forum.
 */
function check_priv_forum_auth($userid, $forumid, $is_posting, $db)
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
	global $phpEx, $db;
	global $FontFace, $FontSize2, $textcolor;
	global $l_jumpto, $l_selectforum, $l_go;
	global $tbl_catagories, $tbl_forums;

	?>
	<FORM ACTION="viewforum.<?php echo $phpEx?>" METHOD="GET">
	<SELECT NAME="forum"><OPTION VALUE="-1"><?php echo $l_selectforum?></OPTION>
	<?php
		$sql = "SELECT cat_id, cat_title
		        FROM `$tbl_catagories`
		        ORDER BY cat_order";

	if($result = mysql_query($sql, $db))
	{
	   $myrow = mysql_fetch_array($result);
	   do {
	      echo "<OPTION VALUE=\"-1\">&nbsp;</OPTION>\n";
	      echo "<OPTION VALUE=\"-1\">$myrow[cat_title]</OPTION>\n";
	      echo "<OPTION VALUE=\"-1\">----------------</OPTION>\n";
	      $sub_sql = "SELECT forum_id, forum_name FROM `$tbl_forums` WHERE cat_id =
	'$myrow[cat_id]' ORDER BY forum_id";
	      if($res = mysql_query($sub_sql, $db)) {
	    if($row = mysql_fetch_array($res)) {
	       do {
		  $name = stripslashes($row[forum_name]);
		  echo "<OPTION VALUE=\"$row[forum_id]\">$name</OPTION>\n";
	       } while($row = mysql_fetch_array($res));
	    }
	    else {
	       echo "<OPTION VALUE=\"0\">No More Forums</OPTION>\n";
	    }
	      }
	      else {
	    echo "<OPTION VALUE=\"0\">Error Connecting to DB</OPTION>\n";
	      }
	   } while($myrow = mysql_fetch_array($result));
	}
	else {
	   echo "<OPTION VALUE=\"-1\">ERROR</OPTION>\n";
	}
	echo "</select>\n<input type=\"submit\" value=\"$l_go\">\n</form>";
}

function language_select($default, $name="language", $dirname="language/")
{
	global $phpEx;
	$dir = opendir($dirname);
	$lang_select = "<select name=\"$name\" id=\"$name\">\n";
	while ($file = readdir($dir))
	{
		if (ereg("^lang_", $file))
		{
			$file = str_replace("lang_", "", $file);
			$file = str_replace(".$phpEx", "", $file);
			$file == $default ? $selected = " SELECTED" : $selected = "";
			$lang_select .= "  <OPTION$selected>$file\n";
		}
	}
	$lang_select .= "</SELECT>\n";
	closedir($dir);
	return $lang_select;
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
	global $phpEx;
	@include('language/lang_'.$sys_lang.'.'.$phpEx);
	$ret_string = $$string;
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

function sync($db, $id, $type)
{
	global $tbl_posts, $tbl_topics, $tbl_forums, $tbl_forums;

	switch($type)
	{
		case 'forum':
			$sql = "SELECT max(post_id) AS last_post 
                    FROM `".$tbl_posts."` 
                    WHERE forum_id = '".$id."'";

			$last_post = claro_sql_query_get_single_value($sql);

			$sql = "SELECT COUNT(post_id) AS total 
                    FROM `".$tbl_posts."` 
                    WHERE forum_id = '".$id."'";

			$total_posts = claro_sql_query_get_single_value($sql);

			$sql = "SELECT count(topic_id) AS total 
                    FROM `".$tbl_topics."` 
                    WHERE forum_id = '".$id."'";

			$total_topics = claro_sql_query_get_single_value($sql);

			$sql = "UPDATE `".$tbl_forums."`
			        SET forum_last_post_id = '$last_post',
			        forum_posts = $total_posts,
			        forum_topics = $total_topics
			        WHERE forum_id = $id";

			$result = claro_sql_query($sql);
		break;

	case 'topic':
		$sql = "SELECT max(post_id) AS last_post 
                FROM `".$tbl_posts."` 
                WHERE topic_id = '".$id."'";

        $last_post = claro_sql_query_get_single_value($sql);

		$sql = "SELECT count(post_id) AS total 
                FROM `".$tbl_posts."` 
                WHERE topic_id = '".$id."'";

	    $total_posts = claro_sql_query_get_single_value($sql);
		$total_posts -= 1;

		$sql = "UPDATE `".$tbl_topics."`
				SET topic_replies = '".$total_posts."', 
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

	return(true);
}

function login_form()
{
    error_die("should display the PHPBB login form ... :-) ");
    // should never happen. but in case of whe should be rapidly warned ....
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
                ." <a href=\"viewtopic.php?topic=".$topicId."&forum=".$forumId."\">"
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

?>